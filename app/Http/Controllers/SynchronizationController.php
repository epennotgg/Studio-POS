<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SynchronizationController extends Controller
{
    // Get latest changes for synchronization
    public function getChanges(Request $request)
    {
        $request->validate([
            'last_sync' => 'required|date',
            'device_id' => 'required|string',
        ]);

        $lastSync = Carbon::parse($request->last_sync);
        
        // Get changes since last sync
        $changes = [
            'products' => $this->getProductChanges($lastSync),
            'transactions' => $this->getTransactionChanges($lastSync),
            'users' => $this->getUserChanges($lastSync),
            'server_time' => now()->toDateTimeString(),
        ];

        return response()->json($changes);
    }

    // Send changes from device to server
    public function sendChanges(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'changes' => 'required|array',
        ]);

        DB::beginTransaction();
        
        try {
            $results = [];
            
            // Process product changes
            if (isset($request->changes['products'])) {
                $results['products'] = $this->processProductChanges($request->changes['products']);
            }
            
            // Process transaction changes
            if (isset($request->changes['transactions'])) {
                $results['transactions'] = $this->processTransactionChanges($request->changes['transactions']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'server_time' => now()->toDateTimeString(),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get product changes since last sync
    private function getProductChanges(Carbon $lastSync)
    {
        return Product::where(function($query) use ($lastSync) {
                $query->where('created_at', '>', $lastSync)
                      ->orWhere('updated_at', '>', $lastSync);
            })
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'type_color' => $product->type_color,
                    'stock' => $product->stock,
                    'buy_price' => $product->buy_price,
                    'price_general' => $product->price_general,
                    'price_agent1' => $product->price_agent1,
                    'price_agent2' => $product->price_agent2,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'deleted_at' => $product->deleted_at,
                ];
            });
    }

    // Get transaction changes since last sync
    private function getTransactionChanges(Carbon $lastSync)
    {
        return Transaction::with(['items', 'user'])
            ->where(function($query) use ($lastSync) {
                $query->where('created_at', '>', $lastSync)
                      ->orWhere('updated_at', '>', $lastSync);
            })
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'invoice_id' => $transaction->invoice_id,
                    'user_id' => $transaction->user_id,
                    'customer_name' => $transaction->customer_name,
                    'customer_phone' => $transaction->customer_phone,
                    'customer_type' => $transaction->customer_type,
                    'payment_method' => $transaction->payment_method,
                    'total_amount' => $transaction->total_amount,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'items' => $transaction->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price_at_transaction' => $item->price_at_transaction,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                ];
            });
    }

    // Get user changes since last sync
    private function getUserChanges(Carbon $lastSync)
    {
        return User::where(function($query) use ($lastSync) {
                $query->where('created_at', '>', $lastSync)
                      ->orWhere('updated_at', '>', $lastSync);
            })
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });
    }

    // Process product changes from device
    private function processProductChanges(array $productChanges)
    {
        $results = [];
        
        foreach ($productChanges as $productData) {
            if (isset($productData['deleted_at']) && $productData['deleted_at']) {
                // Soft delete product
                $product = Product::find($productData['id']);
                if ($product) {
                    $product->delete();
                    $results[] = ['id' => $productData['id'], 'action' => 'deleted'];
                }
            } else {
                // Update or create product
                $product = Product::updateOrCreate(
                    ['id' => $productData['id']],
                    [
                        'name' => $productData['name'],
                        'category_id' => $productData['category_id'],
                        'type_color' => $productData['type_color'],
                        'stock' => $productData['stock'],
                        'buy_price' => $productData['buy_price'],
                        'price_general' => $productData['price_general'],
                        'price_agent1' => $productData['price_agent1'],
                        'price_agent2' => $productData['price_agent2'],
                    ]
                );
                $results[] = ['id' => $product->id, 'action' => $product->wasRecentlyCreated ? 'created' : 'updated'];
            }
        }
        
        return $results;
    }

    // Process transaction changes from device
    private function processTransactionChanges(array $transactionChanges)
    {
        $results = [];
        
        foreach ($transactionChanges as $transactionData) {
            // Create transaction
            $transaction = Transaction::updateOrCreate(
                ['invoice_id' => $transactionData['invoice_id']],
                [
                    'user_id' => $transactionData['user_id'],
                    'customer_name' => $transactionData['customer_name'],
                    'customer_phone' => $transactionData['customer_phone'],
                    'customer_type' => $transactionData['customer_type'],
                    'payment_method' => $transactionData['payment_method'],
                    'total_amount' => $transactionData['total_amount'],
                    'status' => $transactionData['status'],
                ]
            );
            
            // Process transaction items
            if (isset($transactionData['items'])) {
                foreach ($transactionData['items'] as $itemData) {
                    $transaction->items()->updateOrCreate(
                        ['id' => $itemData['id']],
                        [
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'price_at_transaction' => $itemData['price_at_transaction'],
                            'subtotal' => $itemData['subtotal'],
                        ]
                    );
                }
            }
            
            $results[] = ['id' => $transaction->id, 'action' => $transaction->wasRecentlyCreated ? 'created' : 'updated'];
        }
        
        return $results;
    }
}