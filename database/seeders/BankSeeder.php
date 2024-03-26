<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bank;
class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      Bank::create(['card_number'=>'1111111111','account_name'=>'companyAccount','expired_date'=>'2026-07-07','cvv'=>'456']);  
      Bank::create(['card_number'=>'2222222222','account_name'=>'dummyAccount','expired_date'=>'2026-06-06','cvv'=>'123']);
    }
}
