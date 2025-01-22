<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $address  = $this->faker->randomElement(['Lagos', 'Port Harcourt', 'Onitsha', 'Aba', 'Warri']);
        return [
            'customer_type_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'tier_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'sub_region_id' => $this->faker->randomElement([1, 2, 3]),
            'region_id' => 1,
            'business_name' => $this->faker->name . ' Pharmacy',
            'email' => $this->faker->unique()->companyEmail,
            'phone1' => '070326' . $this->faker->randomNumber(5),
            'phone2' => '081256' . $this->faker->randomNumber(5),
            'address' => 'Lagos',
            'street' => $this->faker->randomElement(['Aguleri', 'Akiti', 'Aderigbigbe']),
            'area' => $this->faker->randomElement(['Ajah', 'Okota', 'Festac', 'Ilupeju']),
            'longitude' => $this->faker->randomFloat(10, 20, 5),
            'latitude' => $this->faker->randomFloat(10, 10, 5),
            'registered_by' => 1,
            'relating_officer' => 1
        ];
    }
}
