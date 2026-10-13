<?php

namespace Database\Seeders;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\FlashSale;
use App\Models\HelpTicket;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorPortalSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();
        $buyers = User::where('role', 'buyer')->get();

        if ($vendors->isEmpty()) {
            return;
        }

        $this->seedBankDetails($vendors);
        $this->seedProductMedia($vendors);
        $this->seedOrdersAndPayments($buyers, $vendors);
        $this->seedPayouts($vendors);
        $this->seedMarketing($vendors);
        $this->seedFlashSales($vendors);
        $this->seedSupport($vendors);
        $this->seedVendorNotifications($vendors);
        $this->seedConversations($buyers, $vendors);
        $this->seedReviewReplies();
    }

    private function seedBankDetails($vendors): void
    {
        foreach ($vendors as $vendor) {
            if ($vendor->bank_account_number) {
                continue;
            }

            $vendor->update([
                'bank_name' => 'GTBank',
                'bank_account_name' => $vendor->name,
                'bank_account_number' => '0'.random_int(200000000, 999999999),
            ]);
        }
    }

    private function seedProductMedia($vendors): void
    {
        foreach (Shop::whereIn('user_id', $vendors->pluck('id'))->get() as $shop) {
            foreach ($shop->products as $product) {
                if (! $product->image_url) {
                    $product->update([
                        'image_url' => 'https://picsum.photos/seed/'.Str::slug($product->name).'/600/600',
                    ]);
                }

                if ($product->variants()->exists()) {
                    continue;
                }

                $optionNames = match ($product->category) {
                    'Fabrics', 'Fabrics & Fashion' => ['6 Yards', '12 Yards'],
                    'Auto Parts' => ['Front', 'Rear'],
                    'Phones & Accessories', 'Electronics' => ['Black', 'Blue', 'Space Grey'],
                    default => ['Standard', 'Premium'],
                };

                foreach ($optionNames as $i => $variantName) {
                    if ($i >= 2) {
                        break;
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variantName,
                    ]);
                }
            }
        }
    }

    private function seedOrdersAndPayments($buyers, $vendors): void
    {
        if (OrderItem::exists() || $buyers->isEmpty()) {
            return;
        }

        $statuses = ['confirmed', 'packing', 'out_for_delivery', 'delivered'];
        $seed = Str::slug('mm-vendor-seed', '').'-'.Str::lower(Str::random(4));

        foreach ($vendors as $vendor) {
            $shop = $vendor->shops()->first();

            if (! $shop || $shop->products->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 4; $i++) {
                $buyer = $buyers[$i % max(1, $buyers->count())] ?? $buyers->first();
                $products = $shop->products->random(min(2, $shop->products->count()));

                $items = [];
                $subtotal = 0;

                foreach ($products as $product) {
                    $quantity = random_int(1, 3);
                    $lineTotal = $product->price * $quantity;
                    $subtotal += $lineTotal;

                    $items[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'variant' => $product->variants()->first()?->name ?? '',
                        'price' => $product->price,
                        'quantity' => $quantity,
                        'color' => $product->color,
                        'shop_name' => $shop->name,
                    ];
                }

                $deliveryFee = 1500;
                $status = $statuses[random_int(0, count($statuses) - 1)];
                $method = $i % 2 === 0 ? 'delivery' : 'pickup';

                $order = Order::create([
                    'user_id' => $buyer->id,
                    'order_number' => strtoupper('ORD-'.$seed.'-'.Str::random(6)),
                    'status' => $status,
                    'delivery_method' => $method,
                    'payment_method' => 'paystack',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $subtotal + $deliveryFee,
                    'estimated_delivery' => now()->addDays(2)->format('Y-m-d'),
                    'address_first_name' => Str::before($buyer->name, ' '),
                    'address_last_name' => Str::after($buyer->name, ' ') ?: $buyer->name,
                    'address_phone' => $buyer->phone,
                    'address_street' => fake()->streetAddress(),
                    'address_state' => 'Lagos',
                    'address_city' => 'Lagos',
                ]);

                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                Payment::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'currency' => 'NGN',
                    'method' => 'paystack',
                    'status' => 'completed',
                    'reference' => 'PSK-'.strtoupper(Str::random(12)),
                ]);

                $labels = [
                    'confirmed' => 'Order Confirmed',
                    'packing' => 'Order Being Packed',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Order Delivered',
                ];

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $status,
                    'note' => $labels[$status],
                ]);

                Notification::create([
                    'user_id' => $vendor->id,
                    'type' => 'order',
                    'title' => 'New Order Received',
                    'message' => 'Order '.$order->order_number.' from '.$buyer->name,
                    'read' => false,
                    'link' => '/orders',
                ]);
            }
        }
    }

    private function seedPayouts($vendors): void
    {
        if (Payout::exists()) {
            return;
        }

        foreach ($vendors as $vendor) {
            if ($vendor->status !== 'active') {
                continue;
            }

            $histories = [
                ['days' => 25, 'amount' => 1250000.00, 'status' => 'completed'],
                ['days' => 18, 'amount' => 920000.50, 'status' => 'completed'],
                ['days' => 11, 'amount' => 1340000.25, 'status' => 'completed'],
                ['days' => 1, 'amount' => 480000.00, 'status' => 'processing'],
            ];

            foreach ($histories as $history) {
                Payout::create([
                    'user_id' => $vendor->id,
                    'amount' => $history['amount'],
                    'method' => 'bank_transfer',
                    'account_name' => $vendor->bank_account_name ?? $vendor->name,
                    'account_number' => $vendor->bank_account_number,
                    'bank_name' => $vendor->bank_name,
                    'status' => $history['status'],
                    'note' => 'Weekly settlement',
                    'created_at' => now()->subDays($history['days']),
                ]);
            }
        }
    }

    private function seedMarketing($vendors): void
    {
        if (Coupon::exists()) {
            return;
        }

        $baseCoupons = [
            ['code' => 'WELCOME10', 'title' => 'Welcome Discount', 'discount_type' => 'percentage', 'discount_value' => 10, 'valid_until' => now()->addMonths(6), 'usage_limit' => 100],
            ['code' => 'MINUS5K', 'title' => 'Flat Discount', 'discount_type' => 'fixed', 'discount_value' => 5000, 'valid_until' => now()->addMonth(), 'usage_limit' => 50],
            ['code' => 'FLASH30', 'title' => 'Flash Sale', 'discount_type' => 'percentage', 'discount_value' => 30, 'valid_until' => now()->subWeek(), 'usage_limit' => 200],
        ];

        foreach ($vendors as $vendor) {
            $short = strtoupper(substr(md5($vendor->email), 0, 4));

            foreach ($baseCoupons as $index => $coupon) {
                Coupon::create([
                    'user_id' => $vendor->id,
                    ...$coupon,
                    'code' => $coupon['code'].'-'.$short,
                    'usage_count' => random_int(1, (int) ($coupon['usage_limit'] / 2)),
                    'is_active' => $coupon['valid_until']->isFuture(),
                ]);
            }
        }
    }

    private function seedFlashSales($vendors): void
    {
        if (FlashSale::exists()) {
            return;
        }

        foreach ($vendors as $vendor) {
            if ($vendor->status !== 'active') {
                continue;
            }

            foreach ($vendor->shops as $shop) {
                $product = $shop->products()->inRandomOrder()->first();

                if (! $product) {
                    continue;
                }

                $original = $product->price;

                FlashSale::create([
                    'user_id' => $vendor->id,
                    'product_id' => $product->id,
                    'name' => 'Weekend Special',
                    'original_price' => $original,
                    'sale_price' => (int) round($original * 0.75),
                    'discount_percentage' => 25,
                    'start_time' => now()->subDay(),
                    'end_time' => now()->addDays(2),
                    'sold_count' => random_int(5, 40),
                    'stock_count' => 100,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function seedSupport($vendors): void
    {
        if (HelpTicket::exists() && Faq::exists()) {
            return;
        }

        $activeVendor = User::where('role', 'vendor')->where('status', 'active')->first();

        if ($activeVendor) {
            HelpTicket::create([
                'user_id' => $activeVendor->id,
                'subject' => 'Issue with payout to bank account',
                'message' => 'My payout has been processing for more than 24 hours.',
                'category' => 'Payments',
                'status' => 'open',
                'created_at' => now()->subDays(2),
            ]);

            HelpTicket::create([
                'user_id' => $activeVendor->id,
                'subject' => 'How to bulk upload products?',
                'message' => 'Looking for documentation on bulk product uploads.',
                'category' => 'Products',
                'status' => 'resolved',
                'response' => 'We currently support individual product creation. Bulk uploads via CSV are on the roadmap.',
                'created_at' => now()->subDays(6),
            ]);
        }

        foreach ([
            ['question' => 'How do payouts work?', 'answer' => 'Payouts are processed every Thursday for the previous week\'s earnings. Funds typically take 2-3 business days to reach your connected bank account.', 'category' => 'Payments', 'sort_order' => 1],
            ['question' => 'What are the platform fees?', 'answer' => 'We charge a flat 5% commission on all sales made through the marketplace.', 'category' => 'Payments', 'sort_order' => 2],
            ['question' => 'How do I update my shop details?', 'answer' => 'Go to the Shop tab in your vendor dashboard, edit the fields, and click Save. Changes are reviewed before going live.', 'category' => 'Shop', 'sort_order' => 3],
            ['question' => 'How do I handle returns?', 'answer' => 'When a customer requests a return, you will be notified in your dashboard. Review the request and approve or deny within 48 hours.', 'category' => 'Orders', 'sort_order' => 4],
            ['question' => 'Can I sell digital products?', 'answer' => 'Currently the platform supports physical products. Digital downloads support is planned for a future release.', 'category' => 'General', 'sort_order' => 5],
        ] as $faq) {
            Faq::create([...$faq, 'is_active' => true]);
        }
    }

    private function seedVendorNotifications($vendors): void
    {
        foreach ($vendors as $vendor) {
            if (Notification::where('user_id', $vendor->id)->whereIn('type', ['system', 'payment'])->exists()) {
                continue;
            }

            Notification::create([
                'user_id' => $vendor->id,
                'type' => 'system',
                'title' => 'Welcome to Market Mirror',
                'message' => 'Complete your shop profile to start selling.',
                'read' => true,
                'link' => '/shop',
            ]);

            Notification::create([
                'user_id' => $vendor->id,
                'type' => 'payment',
                'title' => 'Payout Processed',
                'message' => 'Your latest payout has been processed.',
                'read' => false,
                'link' => '/payouts',
            ]);

            Notification::create([
                'user_id' => $vendor->id,
                'type' => 'inventory',
                'title' => 'Low Stock Alert',
                'message' => 'A few of your products are running low on stock.',
                'read' => false,
                'link' => '/inventory',
            ]);
        }
    }

    private function seedConversations($buyers, $vendors): void
    {
        if ($buyers->count() < 2) {
            return;
        }

        $vendorShops = Shop::whereIn('user_id', $vendors->pluck('id'))->get()->keyBy('id');

        foreach ($vendorShops as $shop) {
            $existingIds = ChatConversation::where('shop_id', $shop->id)->pluck('user_id');

            $candidates = $buyers->filter(fn ($buyer) => ! $existingIds->contains($buyer->id))->take(2);

            foreach ($candidates as $buyer) {
                $conversation = ChatConversation::create([
                    'user_id' => $buyer->id,
                    'shop_id' => $shop->id,
                ]);

                $product = $shop->products()->first();

                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender' => 'user',
                    'text' => 'Hi, is this available? '.($product?->name ?? 'Your product'),
                    'read' => true,
                ]);

                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender' => 'vendor',
                    'text' => 'Yes, it is! Would you like to place an order?',
                    'read' => true,
                ]);

                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender' => 'user',
                    'text' => 'Great, sending it now.',
                    'read' => false,
                ]);
            }
        }
    }

    private function seedReviewReplies(): void
    {
        foreach (Review::whereNull('reply')->limit(4)->get() as $index => $review) {
            $review->update([
                'reply' => collect([
                    'Thank you so much for the kind words! We appreciate your support.',
                    'We are glad you loved the product. Come back soon!',
                    'Thank you for the great review. Feedback like this keeps us going.',
                    'Glad it arrived safely. Enjoy!',
                ])->get($index),
            ]);
        }
    }
}
