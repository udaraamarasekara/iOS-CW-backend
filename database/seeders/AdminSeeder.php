<?php

namespace Database\Seeders;

use App\Models\Admin;
//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      User::create(['name'=>'admin','email'=>'admin@google.com','password'=>'admin123','bank_id'=>1]);
      Admin::create(['user_id'=>1]);  
    }
}
