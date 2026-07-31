<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mapper;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Service;
use App\Models\Building;
use App\Models\Task;
use App\Models\ShopLocation;
use App\Models\VendorRegistration;
use App\Models\VerificationQueue;
use App\Models\Category;
use App\Models\Market;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin Users
        $admin1 = User::create([
            'name' => 'Charlie Davis',
            'email' => 'cdavis@admin.com',
            'password' => Hash::make('password'),
            'phone' => '+1 555-0102',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $admin2 = User::create([
            'name' => 'Jane Foster',
            'email' => 'jane.foster@astro.com',
            'password' => Hash::make('password'),
            'phone' => '+1 555-0109',
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create Buyer Users
        $buyers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '+1 555-0100', 'status' => 'active'],
            ['name' => 'Diana Prince', 'email' => 'diana.p@example.com', 'phone' => '+1 555-0103', 'status' => 'pending'],
            ['name' => 'Fiona Gallagher', 'email' => 'fiona.g@example.com', 'phone' => '+1 555-0105', 'status' => 'active'],
            ['name' => 'Hannah Abbott', 'email' => 'hannah.a@example.com', 'phone' => '+1 555-0107', 'status' => 'active'],
            ['name' => 'Ian Malcolm', 'email' => 'ian.m@science.org', 'phone' => '+1 555-0108', 'status' => 'active'],
            ['name' => 'Laura Palmer', 'email' => 'laura.p@twinpeaks.com', 'phone' => '+1 555-0111', 'status' => 'pending'],
        ];

        foreach ($buyers as $buyer) {
            User::create([
                ...$buyer,
                'password' => Hash::make('password'),
                'role' => 'buyer',
            ]);
        }

        // Create Vendor Users
        $vendorUsers = [
            ['name' => 'Bob Smith', 'email' => 'bob.smith@shop.com', 'phone' => '+1 555-0101', 'status' => 'active'],
            ['name' => 'Evan Wright', 'email' => 'ewright@vendor.net', 'phone' => '+1 555-0104', 'status' => 'suspended'],
            ['name' => 'George Miller', 'email' => 'gmiller@market.com', 'phone' => '+1 555-0106', 'status' => 'pending'],
            ['name' => 'Kevin Hart', 'email' => 'kevin.h@comedy.com', 'phone' => '+1 555-0110', 'status' => 'suspended'],
        ];

        $vendorUserModels = [];
        foreach ($vendorUsers as $vu) {
            $vendorUserModels[] = User::create([
                ...$vu,
                'password' => Hash::make('password'),
                'role' => 'vendor',
            ]);
        }

        // Create Shops (from market-mirror-fe mockData)
        $shopsData = [
            ['user_id' => $vendorUserModels[0]->id, 'name' => 'TechCity Electronics', 'category' => 'Electronics', 'rating' => 4.8, 'review_count' => 342, 'product_count' => 4, 'description' => 'Your premium destination for authentic electronics, home theater systems, and smart home devices.', 'location' => 'Zone A Block 12, Alaba International Market', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #1e3a8a, #3b82f6)', 'market' => 'Alaba International Market', 'city' => 'Lagos', 'phone' => '+234 803 123 4567', 'shop_number' => 'Zone A, Suite 12'],
            ['user_id' => $vendorUserModels[0]->id, 'name' => 'PhoneHub Plus', 'category' => 'Phones & Accessories', 'rating' => 4.9, 'review_count' => 892, 'product_count' => 4, 'description' => 'Latest smartphones, tablets, and premium accessories.', 'location' => 'Shop 14, Computer Village', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #4c1d95, #8b5cf6)', 'market' => 'Computer Village', 'city' => 'Lagos', 'phone' => '+234 812 987 6543', 'shop_number' => 'Shop 14'],
            ['user_id' => $vendorUserModels[1]->id, 'name' => 'GlobalFabrics', 'category' => 'Fabrics & Fashion', 'rating' => 4.5, 'review_count' => 128, 'product_count' => 4, 'description' => 'High-quality Ankara, lace, Senator materials, and pure cotton fabrics.', 'location' => 'Hall 3 Shop 22, Trade Fair Complex', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #be123c, #f43f5e)', 'market' => 'Trade Fair Complex', 'city' => 'Lagos', 'phone' => '+234 802 333 4444', 'shop_number' => 'Hall 3, Shop 22'],
            ['user_id' => $vendorUserModels[1]->id, 'name' => 'Kemis Home Appliances', 'category' => 'Home Appliances', 'rating' => 4.2, 'review_count' => 245, 'product_count' => 4, 'description' => 'Durable and affordable home appliances.', 'location' => 'Zone D Block 8, Alaba International Market', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #047857, #10b981)', 'market' => 'Alaba International Market', 'city' => 'Lagos', 'phone' => '+234 807 444 9900', 'shop_number' => 'Zone D, Suite 7'],
            ['user_id' => $vendorUserModels[2]->id, 'name' => 'AutoParts Direct', 'category' => 'Auto Parts', 'rating' => 4.7, 'review_count' => 412, 'product_count' => 4, 'description' => 'Genuine Toyota, Honda, and Mercedes spare parts.', 'location' => 'Zone E Block 15, Alaba International Market', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #b45309, #f59e0b)', 'market' => 'Alaba International Market', 'city' => 'Lagos', 'phone' => '+234 809 666 3344', 'shop_number' => 'Zone E, Suite 15'],
            ['user_id' => $vendorUserModels[3]->id, 'name' => 'BeautyGlow Lagos', 'category' => 'Beauty', 'rating' => 4.6, 'review_count' => 560, 'product_count' => 4, 'description' => 'Original cosmetics, skincare, and fragrances.', 'location' => 'Hall 1 Shop 9, Trade Fair Complex', 'hours' => '8am - 6pm (Mon - Sat)', 'banner_gradient' => 'linear-gradient(135deg, #be185d, #ec4899)', 'market' => 'Trade Fair Complex', 'city' => 'Lagos', 'phone' => '+234 806 222 8811', 'shop_number' => 'Hall 1, Shop 9'],
        ];

        $shopModels = [];
        foreach ($shopsData as $sd) {
            $shopModels[] = Shop::create($sd);
        }

        // Create Products
        $products = [
            ['shop_id' => $shopModels[0]->id, 'name' => 'LG 65-inch 4K Smart TV', 'description' => 'Ultra HD smart television with webOS and Magic Remote.', 'category' => 'Electronics', 'price' => 450000, 'stock' => true, 'rating' => 4.8, 'color' => '#3b82f6'],
            ['shop_id' => $shopModels[0]->id, 'name' => 'Sony PlayStation 5', 'description' => 'Next-gen gaming console with DualSense controller.', 'category' => 'Electronics', 'price' => 380000, 'stock' => true, 'rating' => 4.9, 'color' => '#1e3a8a'],
            ['shop_id' => $shopModels[0]->id, 'name' => 'JBL PartyBox 310', 'description' => 'Portable party speaker with built-in lights.', 'category' => 'Electronics', 'price' => 210000, 'stock' => false, 'rating' => 4.7, 'color' => '#2563eb'],
            ['shop_id' => $shopModels[0]->id, 'name' => 'Hisense 1.5HP Inverter AC', 'description' => 'Energy-saving air conditioner.', 'category' => 'Electronics', 'price' => 185000, 'stock' => true, 'rating' => 4.5, 'color' => '#60a5fa'],

            ['shop_id' => $shopModels[1]->id, 'name' => 'iPhone 15 Pro Max', 'description' => 'Titanium design, A17 Pro chip.', 'category' => 'Phones & Accessories', 'price' => 1250000, 'stock' => true, 'rating' => 4.9, 'color' => '#8b5cf6'],
            ['shop_id' => $shopModels[1]->id, 'name' => 'Samsung Galaxy S24 Ultra', 'description' => 'AI-powered smartphone with S-Pen.', 'category' => 'Phones & Accessories', 'price' => 1150000, 'stock' => true, 'rating' => 4.8, 'color' => '#4c1d95'],
            ['shop_id' => $shopModels[1]->id, 'name' => 'Oraimo Freepods 4', 'description' => 'Active Noise Cancellation wireless earbuds.', 'category' => 'Phones & Accessories', 'price' => 25000, 'stock' => true, 'rating' => 4.4, 'color' => '#a78bfa'],
            ['shop_id' => $shopModels[1]->id, 'name' => 'Anker 20000mAh Power Bank', 'description' => 'Fast-charging portable charger.', 'category' => 'Phones & Accessories', 'price' => 18500, 'stock' => true, 'rating' => 4.6, 'color' => '#7c3aed'],

            ['shop_id' => $shopModels[2]->id, 'name' => 'Premium Swiss Lace (6 Yards)', 'description' => 'High-quality Swiss voil lace.', 'category' => 'Fabrics', 'price' => 35000, 'stock' => true, 'rating' => 4.7, 'color' => '#f43f5e'],
            ['shop_id' => $shopModels[2]->id, 'name' => 'Vlisco Hollandais Ankara', 'description' => 'Original guaranteed Dutch wax print.', 'category' => 'Fabrics', 'price' => 28000, 'stock' => true, 'rating' => 4.8, 'color' => '#be123c'],
            ['shop_id' => $shopModels[2]->id, 'name' => 'Senator Material', 'description' => 'Premium cashmere wool.', 'category' => 'Fabrics', 'price' => 15000, 'stock' => true, 'rating' => 4.5, 'color' => '#fb7185'],
            ['shop_id' => $shopModels[2]->id, 'name' => 'Aso-Oke Gele', 'description' => 'Hand-woven traditional headtie.', 'category' => 'Fabrics', 'price' => 12000, 'stock' => true, 'rating' => 4.6, 'color' => '#e11d48'],

            ['shop_id' => $shopModels[3]->id, 'name' => 'Haier Thermocool Chest Freezer', 'description' => 'Fast-freeze technology.', 'category' => 'Home Appliances', 'price' => 285000, 'stock' => true, 'rating' => 4.5, 'color' => '#10b981'],
            ['shop_id' => $shopModels[3]->id, 'name' => 'Binatone Industrial Fan', 'description' => 'High-speed standing fan.', 'category' => 'Home Appliances', 'price' => 45000, 'stock' => true, 'rating' => 4.3, 'color' => '#047857'],
            ['shop_id' => $shopModels[3]->id, 'name' => 'Kenwood Food Processor', 'description' => 'Multi-functional blender.', 'category' => 'Home Appliances', 'price' => 85000, 'stock' => false, 'rating' => 4.6, 'color' => '#34d399'],
            ['shop_id' => $shopModels[3]->id, 'name' => 'Century Gas Cooker', 'description' => '4-burner table top gas cooker.', 'category' => 'Home Appliances', 'price' => 32000, 'stock' => true, 'rating' => 4.2, 'color' => '#059669'],

            ['shop_id' => $shopModels[4]->id, 'name' => 'Toyota Camry Shock Absorber', 'description' => 'Original front shock absorbers.', 'category' => 'Auto Parts', 'price' => 42000, 'stock' => true, 'rating' => 4.7, 'color' => '#f59e0b'],
            ['shop_id' => $shopModels[4]->id, 'name' => 'Michelin Tyres 205/65R15', 'description' => 'Durable tubeless tyres.', 'category' => 'Auto Parts', 'price' => 65000, 'stock' => true, 'rating' => 4.8, 'color' => '#b45309'],
            ['shop_id' => $shopModels[4]->id, 'name' => 'Bosch Spark Plugs', 'description' => 'Iridium spark plugs.', 'category' => 'Auto Parts', 'price' => 12000, 'stock' => true, 'rating' => 4.5, 'color' => '#fbbf24'],
            ['shop_id' => $shopModels[4]->id, 'name' => 'Honda Accord Brake Pads', 'description' => 'Ceramic brake pads.', 'category' => 'Auto Parts', 'price' => 18500, 'stock' => true, 'rating' => 4.6, 'color' => '#d97706'],

            ['shop_id' => $shopModels[5]->id, 'name' => 'Fenty Beauty Foundation', 'description' => "Pro Filt'r Soft Matte Longwear Foundation.", 'category' => 'Beauty', 'price' => 38000, 'stock' => true, 'rating' => 4.8, 'color' => '#ec4899'],
            ['shop_id' => $shopModels[5]->id, 'name' => "Victoria's Secret Body Mist", 'description' => 'Bare Vanilla fragrance mist.', 'category' => 'Beauty', 'price' => 15000, 'stock' => true, 'rating' => 4.7, 'color' => '#be185d'],
            ['shop_id' => $shopModels[5]->id, 'name' => 'MAC Ruby Woo Lipstick', 'description' => 'Iconic vivid blue-red matte lipstick.', 'category' => 'Beauty', 'price' => 18500, 'stock' => true, 'rating' => 4.9, 'color' => '#f472b6'],
            ['shop_id' => $shopModels[5]->id, 'name' => 'Cerave Hydrating Cleanser', 'description' => 'Face and body wash.', 'category' => 'Beauty', 'price' => 12500, 'stock' => true, 'rating' => 4.6, 'color' => '#db2777'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create Reviews
        $reviews = [
            ['shop_id' => $shopModels[0]->id, 'reviewer_name' => 'Chinedu Okafor', 'rating' => 5, 'text' => 'Bought my TV here. Original product and very reliable.'],
            ['shop_id' => $shopModels[0]->id, 'reviewer_name' => 'Amina Bello', 'rating' => 4, 'text' => 'Good prices compared to the mall.'],
            ['shop_id' => $shopModels[0]->id, 'reviewer_name' => 'Tunde Bakare', 'rating' => 5, 'text' => 'Always authentic with warranty.'],
            ['shop_id' => $shopModels[1]->id, 'reviewer_name' => 'Sarah Johnson', 'rating' => 5, 'text' => 'Swapped my old iPhone here, got a great deal.'],
            ['shop_id' => $shopModels[1]->id, 'reviewer_name' => 'Ibrahim Musa', 'rating' => 4, 'text' => 'Very large selection of phone cases.'],
            ['shop_id' => $shopModels[2]->id, 'reviewer_name' => 'Mrs. Olawale', 'rating' => 5, 'text' => 'The lace quality is superb.'],
            ['shop_id' => $shopModels[3]->id, 'reviewer_name' => 'Mr. Peters', 'rating' => 4, 'text' => 'Bought a washing machine. Working well after 6 months.'],
            ['shop_id' => $shopModels[4]->id, 'reviewer_name' => 'Kazeem', 'rating' => 5, 'text' => 'Genuine parts only.'],
            ['shop_id' => $shopModels[5]->id, 'reviewer_name' => 'Toke M.', 'rating' => 5, 'text' => 'All products are authentic.'],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }

        // Create Services
        $services = [
            ['shop_id' => $shopModels[0]->id, 'name' => 'TV Installation & Setup', 'description' => 'Professional mounting and configuration.', 'category' => 'Installation', 'price' => 15000, 'rating' => 4.8],
            ['shop_id' => $shopModels[1]->id, 'name' => 'Phone Screen Repair', 'description' => 'Fast screen replacement.', 'category' => 'Repair', 'price' => 25000, 'rating' => 4.9],
            ['shop_id' => $shopModels[2]->id, 'name' => 'Fabric Tailoring', 'description' => 'Custom fitting and sewing.', 'category' => 'Tailoring', 'price' => 10000, 'rating' => 4.5],
            ['shop_id' => $shopModels[3]->id, 'name' => 'AC Installation & Servicing', 'description' => 'Professional installation and maintenance.', 'category' => 'Maintenance', 'price' => 18000, 'rating' => 4.7],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Create Buildings
        Building::create(['name' => 'Alaba International Market', 'description' => 'The largest electronics and home appliance market in West Africa.', 'location' => 'Ojo, Lagos', 'shop_count' => 5000, 'category' => 'Market Complex', 'banner_gradient' => 'linear-gradient(135deg, #1e3a8a, #3b82f6)']);
        Building::create(['name' => 'Trade Fair Complex', 'description' => 'Major hub for cosmetics, jewelry, fabrics, and fashion.', 'location' => 'Badagry Expressway, Lagos', 'shop_count' => 3500, 'category' => 'Market Complex', 'banner_gradient' => 'linear-gradient(135deg, #4c1d95, #8b5cf6)']);
        Building::create(['name' => 'Computer Village', 'description' => 'The prime ICT accessories and mobile phone hub.', 'location' => 'Ikeja, Lagos', 'shop_count' => 4000, 'category' => 'Market Complex', 'banner_gradient' => 'linear-gradient(135deg, #047857, #10b981)']);

        // Create Mapper Users
        $mapperData = [
            ['name' => 'Amaka Obi', 'email' => 'amaka.obi@marketmirror.com', 'phone' => '+234 802 111 2233', 'employee_id' => 'EMP-2201', 'market' => 'Balogun Market', 'region' => 'Lagos Island', 'status' => 'active'],
            ['name' => 'Tunde Bakare', 'email' => 'tunde.bakare@marketmirror.com', 'phone' => '+234 803 222 3344', 'employee_id' => 'EMP-2202', 'market' => 'Alaba International Market', 'region' => 'Lagos Mainland', 'status' => 'active'],
            ['name' => 'Ngozi Umeh', 'email' => 'ngozi.umeh@marketmirror.com', 'phone' => '+234 805 333 4455', 'employee_id' => 'EMP-2203', 'market' => 'Onitsha Main Market', 'region' => 'South East', 'status' => 'active'],
            ['name' => 'Ibrahim Musa', 'email' => 'ibrahim.musa@marketmirror.com', 'phone' => '+234 806 444 5566', 'employee_id' => 'EMP-2204', 'market' => 'Kurmi Market', 'region' => 'North West', 'status' => 'active'],
            ['name' => 'Fatima Bello', 'email' => 'fatima.bello@marketmirror.com', 'phone' => '+234 808 666 7788', 'employee_id' => 'EMP-2206', 'market' => 'Wuse Market', 'region' => 'Abuja FCT', 'status' => 'active'],
            ['name' => 'Emeka Chukwu', 'email' => 'emeka.chukwu@marketmirror.com', 'phone' => '+234 809 777 8899', 'employee_id' => 'EMP-2207', 'market' => 'Balogun Market', 'region' => 'Lagos Island', 'status' => 'active'],
            ['name' => 'Grace Adeyemi', 'email' => 'grace.adeyemi@marketmirror.com', 'phone' => '+234 810 888 9900', 'employee_id' => 'EMP-2208', 'market' => 'Mile 12 Market', 'region' => 'Lagos Mainland', 'status' => 'active'],
            ['name' => 'Blessing Okafor', 'email' => 'blessing.okafor@marketmirror.com', 'phone' => '+234 812 000 1122', 'employee_id' => 'EMP-2210', 'market' => 'New Benin Market', 'region' => 'South South', 'status' => 'active'],
        ];

        foreach ($mapperData as $md) {
            $user = User::create([
                'name' => $md['name'],
                'email' => $md['email'],
                'password' => Hash::make('password'),
                'phone' => $md['phone'],
                'role' => 'mapper',
                'status' => $md['status'],
                'employee_id' => $md['employee_id'],
                'region' => $md['region'],
            ]);

            $tasksAssigned = rand(15, 61);
            $tasksCompleted = (int) ($tasksAssigned * (rand(80, 98) / 100));
            $tasksPending = $tasksAssigned - $tasksCompleted - rand(0, 3);
            $tasksRejected = $tasksAssigned - $tasksCompleted - $tasksPending;

            Mapper::create([
                'user_id' => $user->id,
                'employee_id' => $md['employee_id'],
                'assigned_market' => $md['market'],
                'assigned_region' => $md['region'],
                'mapper_status' => $md['status'],
                'gps_status' => collect(['online', 'offline', 'idle'])->random(),
                'tasks_assigned' => $tasksAssigned,
                'tasks_completed' => max(0, $tasksCompleted),
                'tasks_pending' => max(0, $tasksPending),
                'tasks_rejected' => max(0, $tasksRejected),
                'approval_rate' => rand(68, 98),
                'avg_completion_time_hrs' => rand(17, 42) / 10,
                'distance_covered_km' => rand(40, 210),
                'supervisor' => collect(['Chidi Eze', 'Bola Fashola'])->random(),
                'last_login_at' => now()->subHours(rand(1, 48)),
            ]);
        }

        // Create Tasks
        $mappers = Mapper::all();
        foreach ($mappers as $mapper) {
            $taskCount = rand(1, 3);
            for ($i = 0; $i < $taskCount; $i++) {
                Task::create([
                    'mapper_id' => $mapper->id,
                    'title' => collect(['Vendor Registration', 'Shop Mapping', 'Re-mapping', 'Verification Visit'])->random(),
                    'type' => collect(['vendor_registration', 'shop_mapping', 're_mapping', 'verification_visit'])->random(),
                    'market' => $mapper->assigned_market,
                    'priority' => collect(['low', 'medium', 'high', 'urgent'])->random(),
                    'status' => collect(['pending', 'in_progress', 'completed', 'rejected'])->random(),
                    'due_date' => now()->addDays(rand(1, 7)),
                    'assigned_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // Create Shop Locations
        ShopLocation::create(['vendor_name' => 'Mama Ijoke Provisions', 'market' => 'Balogun Market', 'street' => 'Martin Street', 'shop_block' => 'Block A3', 'latitude' => 6.4547, 'longitude' => 3.3921, 'accuracy' => 'high', 'status' => 'pending', 'mapper_name' => 'Amaka Obi']);
        ShopLocation::create(['vendor_name' => 'Chuks Electronics', 'market' => 'Alaba International Market', 'street' => 'Amuwo Road', 'shop_block' => 'Block C1', 'latitude' => 6.4698, 'longitude' => 3.1997, 'accuracy' => 'high', 'status' => 'approved', 'mapper_name' => 'Tunde Bakare']);
        ShopLocation::create(['vendor_name' => 'Aisha Spices & Grains', 'market' => 'Wuse Market', 'street' => 'Aminu Kano Crescent', 'shop_block' => 'Block D4', 'latitude' => 9.0643, 'longitude' => 7.4892, 'accuracy' => 'high', 'status' => 'approved', 'mapper_name' => 'Fatima Bello']);

        // Create Vendor Registrations
        VendorRegistration::create(['vendor_name' => 'Mama Ijoke Provisions', 'business_category' => 'Groceries', 'market' => 'Balogun Market', 'mapper_id' => $mappers[0]->id, 'mapper_name' => 'Amaka Obi', 'current_status' => 'submitted', 'approval_status' => 'pending_review', 'latitude' => 6.4547, 'longitude' => 3.3921]);
        VendorRegistration::create(['vendor_name' => 'Chuks Electronics', 'business_category' => 'Electronics', 'market' => 'Alaba International Market', 'mapper_id' => $mappers[1]->id, 'mapper_name' => 'Tunde Bakare', 'current_status' => 'finalized', 'approval_status' => 'approved', 'latitude' => 6.4698, 'longitude' => 3.1997]);

        // Create Verification Queue
        VerificationQueue::create(['vendor_name' => 'Mama Ijoke Provisions', 'market' => 'Balogun Market', 'mapper_id' => $mappers[0]->id, 'mapper_name' => 'Amaka Obi', 'latitude' => 6.4547, 'longitude' => 3.3921, 'status' => 'pending', 'notes' => 'Storefront and interior photos captured.']);
        VerificationQueue::create(['vendor_name' => "Blessing's Boutique", 'market' => 'New Benin Market', 'mapper_id' => $mappers[7]->id, 'mapper_name' => 'Blessing Okafor', 'latitude' => 6.3350, 'longitude' => 5.6037, 'status' => 'approved', 'notes' => 'Fully documented submission.']);

        // Create Categories
        $categories = ['Electronics', 'Phones & Accessories', 'Fabrics & Fashion', 'Home Appliances', 'Auto Parts', 'Beauty', 'Groceries', 'Fashion', 'Books', 'Pets'];
        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
                'products_count' => Product::where('category', 'like', "%{$cat}%")->count(),
            ]);
        }

        // Create Markets
        Market::create(['name' => 'Alaba International Market', 'description' => 'The largest electronics market in West Africa.', 'location' => 'Ojo, Lagos', 'city' => 'Lagos', 'shop_count' => 5000, 'category' => 'Market Complex']);
        Market::create(['name' => 'Computer Village', 'description' => 'The prime ICT hub of Nigeria.', 'location' => 'Ikeja, Lagos', 'city' => 'Lagos', 'shop_count' => 4000, 'category' => 'Market Complex']);
        Market::create(['name' => 'Trade Fair Complex', 'description' => 'Major hub for cosmetics and fabrics.', 'location' => 'Badagry Expressway, Lagos', 'city' => 'Lagos', 'shop_count' => 3500, 'category' => 'Market Complex']);

        // Create Orders
        $buyerUsers = User::where('role', 'buyer')->get();
        foreach ($buyerUsers->take(3) as $buyer) {
            Order::create([
                'user_id' => $buyer->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => collect(['confirmed', 'packing', 'out_for_delivery', 'delivered'])->random(),
                'delivery_method' => collect(['delivery', 'pickup'])->random(),
                'subtotal' => rand(15000, 500000),
                'delivery_fee' => 1500,
                'total' => rand(16500, 501500),
                'payment_method' => 'paystack',
            ]);
        }
    }
}
