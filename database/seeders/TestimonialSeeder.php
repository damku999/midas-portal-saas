<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Central\Testimonial;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial testimonials from hardcoded home page data
        $testimonials = [
            [
                'name' => 'Rajesh Kumar',
                'company' => 'Shield Insurance',
                'role' => 'Director',
                'testimonial' => 'Midas Portal transformed our agency operations. The WhatsApp integration alone saves us 10+ hours weekly. ROI was immediate!',
                'rating' => 5,
                'status' => 'active',
                'display_order' => 1,
            ],
            [
                'name' => 'Priya Sharma',
                'company' => 'SecureLife Agency',
                'role' => 'CEO',
                'testimonial' => 'Best insurance software in India! The customer portal reduced support calls by 60%. Our clients love the self-service features.',
                'rating' => 5,
                'status' => 'active',
                'display_order' => 2,
            ],
            [
                'name' => 'Amit Patel',
                'company' => 'Prime Insurance',
                'role' => 'Owner',
                'testimonial' => 'The automated reminders and analytics helped us increase renewal rates by 35%. Excellent platform with outstanding support!',
                'rating' => 5,
                'status' => 'active',
                'display_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            // Check if testimonial already exists by name and company
            $existing = Testimonial::where('name', $testimonial['name'])
                ->where('company', $testimonial['company'])
                ->first();

            if (!$existing) {
                Testimonial::create($testimonial);
            }
        }

        $this->command->info('Testimonials seeded successfully!');
    }
}
