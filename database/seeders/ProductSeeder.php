<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Insert Categories (Makanan & Minuman)
        $categories = [
            ['slug' => Str::slug('Makanan'), 'name' => 'Makanan'],
            ['slug' => Str::slug('Minuman'), 'name' => 'Minuman'],
        ];

        DB::table('categories')->insert($categories);

        $categoryIds = DB::table('categories')->pluck('id', 'name');

        // Buat beberapa Supplier dummy
        $supplierIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'slug' => Str::slug($faker->company . '-' . $i),
                'name' => $faker->company,
                'address' => $faker->address,
                'phone' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'city' => $faker->city,
                'province' => $faker->state,
                'postal_code' => $faker->postcode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Buat 10 produk dummy
        for ($i = 1; $i <= 30; $i++) {
            $categoryName = $faker->randomElement(['Makanan', 'Minuman']);
            $categoryId = $categoryIds[$categoryName];
            $supplierId = $faker->randomElement($supplierIds);

            $name = $categoryName === 'Makanan'
                ? $faker->randomElement(['Keripik Singkong', 'Roti Bakar', 'Nasi Goreng', 'Bakso', 'Mie Ayam'])
                : $faker->randomElement(['Es Teh', 'Kopi Hitam', 'Jus Jeruk', 'Susu Cokelat', 'Teh Botol']);

            $productId = DB::table('products')->insertGetId([
                'slug' => Str::slug($name . '-' . $i),
                'name' => $name,
                'price_buy' => $faker->numberBetween(5000, 20000),
                'price_sale' => $faker->numberBetween(15000, 50000),
                'stock' => $faker->numberBetween(10, 100),
                'image' => null,
                'description' => $faker->sentence,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert stok awal
            DB::table('product_stocks')->insert([
                'product_id' => $productId,
                'date' => Carbon::now()->toDateString(),
                'stock' => $faker->numberBetween(10, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
