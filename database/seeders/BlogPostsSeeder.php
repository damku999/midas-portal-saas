<?php

namespace Database\Seeders;

use App\Models\Central\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            // 10 Product Updates / Feature Usage Posts
            [
                'title' => 'How to Manage Customer Families in Midas Portal',
                'category' => 'insurance-tips',
                'excerpt' => 'Learn how to efficiently manage family members and group policies using our Family Management feature.',
                'content' => $this->getFeatureContent('family'),
                'tags' => ['customer-management', 'family-management', 'features'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Streamline Policy Renewals with Automated Reminders',
                'category' => 'insurance-tips',
                'excerpt' => 'Discover how automated WhatsApp and email reminders can help you never miss a policy renewal again.',
                'content' => $this->getFeatureContent('renewals'),
                'tags' => ['policy-management', 'automation', 'whatsapp'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Mastering the Quotation System for Quick Proposals',
                'category' => 'insurance-tips',
                'excerpt' => 'Generate professional insurance quotations in minutes with our intuitive quotation builder.',
                'content' => $this->getFeatureContent('quotations'),
                'tags' => ['quotations', 'sales', 'features'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'WhatsApp Integration: Connect with Customers Instantly',
                'category' => 'product-updates',
                'excerpt' => 'How WhatsApp Business API integration transforms customer communication for insurance agencies.',
                'content' => $this->getFeatureContent('whatsapp'),
                'tags' => ['whatsapp', 'communication', 'integration'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Using Analytics Dashboard for Business Insights',
                'category' => 'insurance-tips',
                'excerpt' => 'Leverage powerful analytics to make data-driven decisions and grow your insurance business.',
                'content' => $this->getFeatureContent('analytics'),
                'tags' => ['analytics', 'reports', 'business-intelligence'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Commission Tracking Made Easy: A Complete Guide',
                'category' => 'insurance-tips',
                'excerpt' => 'Track agent commissions accurately and automate calculations with our commission management system.',
                'content' => $this->getFeatureContent('commissions'),
                'tags' => ['commissions', 'payments', 'features'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Document Management: Organize Insurance Files Efficiently',
                'category' => 'insurance-tips',
                'excerpt' => 'Store, organize, and share insurance documents securely with our document management system.',
                'content' => $this->getFeatureContent('documents'),
                'tags' => ['documents', 'organization', 'storage'],
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'Customer Portal: Empower Your Clients with Self-Service',
                'category' => 'insurance-tips',
                'excerpt' => 'Give your customers 24/7 access to their policies, claims, and documents through a dedicated portal.',
                'content' => $this->getFeatureContent('customer-portal'),
                'tags' => ['customer-portal', 'self-service', 'features'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Lead Management: Convert Prospects into Customers',
                'category' => 'insurance-tips',
                'excerpt' => 'Optimize your sales funnel with effective lead tracking and follow-up management.',
                'content' => $this->getFeatureContent('leads'),
                'tags' => ['lead-management', 'sales', 'crm'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Multi-Tenant Architecture: Why It Matters for Insurance Agencies',
                'category' => 'product-updates',
                'excerpt' => 'Understanding the security and scalability benefits of our multi-tenant platform.',
                'content' => $this->getFeatureContent('multi-tenant'),
                'tags' => ['architecture', 'security', 'scalability'],
                'reading_time_minutes' => 7,
            ],

            // 5 Claims-Related Posts
            [
                'title' => 'Complete Guide to Filing Insurance Claims',
                'category' => 'claims',
                'excerpt' => 'Step-by-step process for filing and tracking insurance claims efficiently.',
                'content' => $this->getClaimsContent('filing'),
                'tags' => ['claims', 'process', 'guide'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Common Mistakes to Avoid in Claims Processing',
                'category' => 'claims',
                'excerpt' => 'Learn from common pitfalls that delay claim settlements and how to avoid them.',
                'content' => $this->getClaimsContent('mistakes'),
                'tags' => ['claims', 'best-practices', 'tips'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Claim Documentation: What Documents Do You Need?',
                'category' => 'claims',
                'excerpt' => 'Comprehensive checklist of documents required for different types of insurance claims.',
                'content' => $this->getClaimsContent('documentation'),
                'tags' => ['claims', 'documents', 'requirements'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Understanding Claim Settlement Timeline',
                'category' => 'claims',
                'excerpt' => 'What to expect during the claim settlement process and typical timelines for different insurance types.',
                'content' => $this->getClaimsContent('timeline'),
                'tags' => ['claims', 'settlement', 'timeline'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'How to Handle Claim Rejections and Appeals',
                'category' => 'claims',
                'excerpt' => 'Understanding why claims get rejected and the step-by-step appeal process.',
                'content' => $this->getClaimsContent('rejections'),
                'tags' => ['claims', 'appeals', 'rejection'],
                'reading_time_minutes' => 9,
            ],

            // 15 Insurance Add-ons Posts
            [
                'title' => 'Zero Depreciation Cover: Is It Worth the Extra Cost?',
                'category' => 'addons',
                'excerpt' => 'Comprehensive analysis of zero depreciation add-on for motor insurance and when you should buy it.',
                'content' => $this->getAddonContent('zero-depreciation'),
                'tags' => ['motor-insurance', 'addons', 'depreciation'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Personal Accident Cover: Essential Protection Add-On',
                'category' => 'addons',
                'excerpt' => 'Why personal accident cover is crucial and what it includes beyond basic motor insurance.',
                'content' => $this->getAddonContent('personal-accident'),
                'tags' => ['motor-insurance', 'addons', 'personal-accident'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Engine Protection Cover: Safeguard Your Vehicle',
                'category' => 'addons',
                'excerpt' => 'Understanding engine protection add-on and how it saves you from expensive engine repairs.',
                'content' => $this->getAddonContent('engine-protection'),
                'tags' => ['motor-insurance', 'addons', 'engine'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'NCB Protection: Preserve Your No Claim Bonus',
                'category' => 'addons',
                'excerpt' => 'Learn how NCB protection works and why it\'s valuable for safe drivers.',
                'content' => $this->getAddonContent('ncb-protection'),
                'tags' => ['motor-insurance', 'addons', 'ncb'],
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'Return to Invoice Cover: Maximum Protection',
                'category' => 'addons',
                'excerpt' => 'Get full invoice value in case of total loss with this comprehensive add-on.',
                'content' => $this->getAddonContent('return-to-invoice'),
                'tags' => ['motor-insurance', 'addons', 'total-loss'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Critical Illness Rider: Enhanced Health Protection',
                'category' => 'addons',
                'excerpt' => 'How critical illness riders provide additional financial security during major health crises.',
                'content' => $this->getAddonContent('critical-illness'),
                'tags' => ['health-insurance', 'addons', 'critical-illness'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Hospital Cash Benefit: Daily Income During Hospitalization',
                'category' => 'addons',
                'excerpt' => 'Understanding hospital cash add-on and how it covers non-medical expenses.',
                'content' => $this->getAddonContent('hospital-cash'),
                'tags' => ['health-insurance', 'addons', 'hospitalization'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Maternity Cover: Planning for Parenthood',
                'category' => 'addons',
                'excerpt' => 'Complete guide to maternity add-on coverage, waiting periods, and benefits.',
                'content' => $this->getAddonContent('maternity'),
                'tags' => ['health-insurance', 'addons', 'maternity'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Consumables Cover: Extra Medical Expense Protection',
                'category' => 'addons',
                'excerpt' => 'Why consumables cover matters and what medical items it includes.',
                'content' => $this->getAddonContent('consumables'),
                'tags' => ['health-insurance', 'addons', 'medical-expenses'],
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'Roadside Assistance: Never Get Stranded',
                'category' => 'addons',
                'excerpt' => 'Benefits of roadside assistance add-on and what services are typically included.',
                'content' => $this->getAddonContent('roadside-assistance'),
                'tags' => ['motor-insurance', 'addons', 'assistance'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Key Replacement Cover: Lost Key Protection',
                'category' => 'addons',
                'excerpt' => 'How key replacement add-on protects you from expensive key and lock system replacements.',
                'content' => $this->getAddonContent('key-replacement'),
                'tags' => ['motor-insurance', 'addons', 'keys'],
                'reading_time_minutes' => 3,
            ],
            [
                'title' => 'Tyre Protection: Extend Your Tyre Life',
                'category' => 'addons',
                'excerpt' => 'Understanding tyre and rim protection cover for comprehensive vehicle care.',
                'content' => $this->getAddonContent('tyre-protection'),
                'tags' => ['motor-insurance', 'addons', 'tyres'],
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'Geographical Extension: Coverage Beyond India',
                'category' => 'addons',
                'excerpt' => 'Extend your vehicle coverage to neighboring countries with geographical extension.',
                'content' => $this->getAddonContent('geographical-extension'),
                'tags' => ['motor-insurance', 'addons', 'international'],
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'Passenger Cover: Protect Everyone in Your Vehicle',
                'category' => 'addons',
                'excerpt' => 'Why passenger cover is essential for family vehicles and what it includes.',
                'content' => $this->getAddonContent('passenger-cover'),
                'tags' => ['motor-insurance', 'addons', 'passengers'],
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'Emergency Medical Evacuation: Critical Care Coverage',
                'category' => 'addons',
                'excerpt' => 'Understanding emergency evacuation riders and when they become crucial.',
                'content' => $this->getAddonContent('emergency-evacuation'),
                'tags' => ['health-insurance', 'addons', 'emergency'],
                'reading_time_minutes' => 6,
            ],

            // 20 Insurance Types Posts
            [
                'title' => 'Comprehensive Guide to Health Insurance in India',
                'category' => 'insurance-types',
                'excerpt' => 'Everything you need to know about health insurance plans, coverage, and benefits.',
                'content' => $this->getInsuranceTypeContent('health'),
                'tags' => ['health-insurance', 'guide', 'india'],
                'reading_time_minutes' => 10,
            ],
            [
                'title' => 'Term Life Insurance: Complete Buying Guide',
                'category' => 'insurance-types',
                'excerpt' => 'How to choose the right term insurance plan for your family\'s financial security.',
                'content' => $this->getInsuranceTypeContent('term-life'),
                'tags' => ['life-insurance', 'term-insurance', 'guide'],
                'reading_time_minutes' => 9,
            ],
            [
                'title' => 'Motor Insurance Explained: Third-Party vs Comprehensive',
                'category' => 'insurance-types',
                'excerpt' => 'Understanding different types of motor insurance and which one suits your needs.',
                'content' => $this->getInsuranceTypeContent('motor'),
                'tags' => ['motor-insurance', 'comparison', 'guide'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Travel Insurance: Essential for Every Traveler',
                'category' => 'insurance-types',
                'excerpt' => 'Why travel insurance is crucial and what it covers for domestic and international trips.',
                'content' => $this->getInsuranceTypeContent('travel'),
                'tags' => ['travel-insurance', 'guide', 'coverage'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Home Insurance: Protect Your Biggest Investment',
                'category' => 'insurance-types',
                'excerpt' => 'Comprehensive guide to home insurance coverage, benefits, and claims process.',
                'content' => $this->getInsuranceTypeContent('home'),
                'tags' => ['home-insurance', 'property', 'guide'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Critical Illness Insurance: Financial Security When It Matters',
                'category' => 'insurance-types',
                'excerpt' => 'Understanding critical illness plans and why they complement health insurance.',
                'content' => $this->getInsuranceTypeContent('critical-illness'),
                'tags' => ['critical-illness', 'health', 'guide'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Personal Accident Insurance: Coverage Beyond Health',
                'category' => 'insurance-types',
                'excerpt' => 'Why personal accident insurance is essential and what benefits it provides.',
                'content' => $this->getInsuranceTypeContent('personal-accident'),
                'tags' => ['personal-accident', 'insurance', 'guide'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Fire Insurance for Businesses: Comprehensive Protection',
                'category' => 'insurance-types',
                'excerpt' => 'How fire insurance protects your business assets and ensures business continuity.',
                'content' => $this->getInsuranceTypeContent('fire'),
                'tags' => ['fire-insurance', 'business', 'commercial'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Marine Insurance: Protecting Your Cargo and Shipments',
                'category' => 'insurance-types',
                'excerpt' => 'Understanding marine insurance for goods in transit and international trade.',
                'content' => $this->getInsuranceTypeContent('marine'),
                'tags' => ['marine-insurance', 'cargo', 'trade'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Group Health Insurance: Employee Benefits Explained',
                'category' => 'insurance-types',
                'excerpt' => 'How group health insurance works and benefits for employers and employees.',
                'content' => $this->getInsuranceTypeContent('group-health'),
                'tags' => ['group-insurance', 'employee-benefits', 'corporate'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Professional Indemnity Insurance for Service Providers',
                'category' => 'insurance-types',
                'excerpt' => 'Essential coverage for professionals against negligence claims and legal costs.',
                'content' => $this->getInsuranceTypeContent('professional-indemnity'),
                'tags' => ['professional-indemnity', 'business', 'liability'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Cyber Insurance: Protecting Your Digital Assets',
                'category' => 'insurance-types',
                'excerpt' => 'Why cyber insurance is crucial in today\'s digital age and what it covers.',
                'content' => $this->getInsuranceTypeContent('cyber'),
                'tags' => ['cyber-insurance', 'digital', 'data-protection'],
                'reading_time_minutes' => 9,
            ],
            [
                'title' => 'Directors and Officers Liability Insurance',
                'category' => 'insurance-types',
                'excerpt' => 'Protecting company leadership from personal liability in business decisions.',
                'content' => $this->getInsuranceTypeContent('dno'),
                'tags' => ['dno-insurance', 'corporate', 'liability'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Commercial Vehicle Insurance: Fleet Management',
                'category' => 'insurance-types',
                'excerpt' => 'Comprehensive guide to insuring commercial vehicles and fleet operations.',
                'content' => $this->getInsuranceTypeContent('commercial-vehicle'),
                'tags' => ['commercial-vehicle', 'fleet', 'business'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Liability Insurance: Protecting Against Third-Party Claims',
                'category' => 'insurance-types',
                'excerpt' => 'Understanding general liability insurance and when businesses need it.',
                'content' => $this->getInsuranceTypeContent('liability'),
                'tags' => ['liability-insurance', 'business', 'protection'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Workers Compensation Insurance: Employee Protection',
                'category' => 'insurance-types',
                'excerpt' => 'Legal requirements and benefits of workers compensation insurance.',
                'content' => $this->getInsuranceTypeContent('workers-comp'),
                'tags' => ['workers-compensation', 'employee', 'legal'],
                'reading_time_minutes' => 8,
            ],
            [
                'title' => 'Family Floater Health Insurance: One Policy for All',
                'category' => 'insurance-types',
                'excerpt' => 'Benefits of family floater plans and how to choose the right sum insured.',
                'content' => $this->getInsuranceTypeContent('family-floater'),
                'tags' => ['family-floater', 'health-insurance', 'family'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Senior Citizen Health Insurance: Coverage for Elderly',
                'category' => 'insurance-types',
                'excerpt' => 'Specialized health insurance options and considerations for senior citizens.',
                'content' => $this->getInsuranceTypeContent('senior-citizen'),
                'tags' => ['senior-citizen', 'health-insurance', 'elderly'],
                'reading_time_minutes' => 7,
            ],
            [
                'title' => 'Student Insurance: Protection for Educational Journey',
                'category' => 'insurance-types',
                'excerpt' => 'Understanding insurance needs for students studying in India and abroad.',
                'content' => $this->getInsuranceTypeContent('student'),
                'tags' => ['student-insurance', 'education', 'international'],
                'reading_time_minutes' => 6,
            ],
            [
                'title' => 'Crop Insurance: Farmer Financial Security',
                'category' => 'insurance-types',
                'excerpt' => 'How crop insurance protects farmers from weather and yield risks.',
                'content' => $this->getInsuranceTypeContent('crop'),
                'tags' => ['crop-insurance', 'agriculture', 'farmers'],
                'reading_time_minutes' => 8,
            ],
        ];

        foreach ($posts as $index => $post) {
            $publishedDate = now()->subDays(rand(1, 365));

            BlogPost::create([
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'excerpt' => $post['excerpt'],
                'content' => $post['content'],
                'category' => $post['category'],
                'tags' => $post['tags'],
                'meta_title' => $post['title'] . ' | Midas Portal Blog',
                'meta_description' => $post['excerpt'],
                'meta_keywords' => implode(', ', $post['tags']),
                'status' => 'published',
                'published_at' => $publishedDate,
                'reading_time_minutes' => $post['reading_time_minutes'],
                'views_count' => rand(50, 5000),
            ]);
        }
    }

    private function getFeatureContent($feature)
    {
        $content = [
            'family' => '<h2>Introduction to Family Management</h2><p>Managing insurance policies for an entire family can be complex. Midas Portal\'s Family Management feature simplifies this by allowing you to group family members and track all their policies in one place.</p><h3>Key Benefits</h3><ul><li>Centralized family member profiles</li><li>Easy policy assignment and tracking</li><li>Family floater policy management</li><li>Consolidated premium tracking</li><li>Joint renewal reminders</li></ul><h3>How to Use Family Management</h3><p>Start by creating a primary customer account, then add family members with their relationships. Link existing policies or create new ones for the entire family. The system automatically tracks relationships and provides family-wide insights.</p><h3>Best Practices</h3><p>Always keep family member information updated, especially contact details and medical history. Use the family view dashboard to get a complete picture of coverage gaps and renewal schedules.</p>',

            'renewals' => '<h2>Never Miss a Renewal Again</h2><p>Policy renewals are critical for maintaining continuous insurance coverage. Our automated reminder system ensures you and your customers never miss important renewal dates.</p><h3>Automated Notification System</h3><ul><li>WhatsApp reminders 60, 30, and 15 days before expiry</li><li>Email notifications with renewal quotes</li><li>SMS alerts for immediate attention</li><li>Customer portal notifications</li></ul><h3>Customization Options</h3><p>Configure reminder frequency, channels, and message templates according to your agency\'s needs. Personalize messages for different customer segments.</p><h3>Impact on Business</h3><p>Agencies using our renewal system report 40% improvement in renewal rates and significantly reduced policy lapses. Automated reminders free up your team to focus on customer service rather than manual follow-ups.</p>',

            'quotations' => '<h2>Professional Quotations in Minutes</h2><p>Create accurate, professional insurance quotations quickly with our comprehensive quotation builder. Impress prospects and close deals faster.</p><h3>Features</h3><ul><li>Pre-configured insurance product templates</li><li>Automatic premium calculations</li><li>Add-on and rider integration</li><li>Professional PDF generation</li><li>Comparison tables</li><li>Email and WhatsApp sharing</li></ul><h3>Using the Quotation Builder</h3><p>Select the insurance type, enter customer details, choose coverage options, and the system automatically calculates premiums based on your configured rates. Add recommended riders and generate a professional PDF in seconds.</p><h3>Conversion Tracking</h3><p>Track quotation status from sent to viewed, discussed, and converted. Follow up automatically with customers who viewed but haven\'t responded.</p>',
        ];

        return $content[$feature] ?? '<p>Comprehensive guide content about ' . $feature . '</p>';
    }

    private function getClaimsContent($type)
    {
        $content = [
            'filing' => '<h2>Filing Insurance Claims: Step-by-Step Guide</h2><p>Understanding the claims process is crucial for quick and hassle-free settlements. Here\'s everything you need to know.</p><h3>Immediate Steps After an Incident</h3><ol><li>Inform the insurance company within 24 hours</li><li>Preserve evidence (photos, videos, documents)</li><li>File FIR if required (for theft, accidents)</li><li>Get medical treatment documentation</li><li>Avoid admitting liability</li></ol><h3>Documentation Required</h3><ul><li>Claim form (duly filled and signed)</li><li>Original policy document</li><li>Identity proof</li><li>Incident-specific documents</li><li>Medical bills and reports (health claims)</li><li>Repair estimates (motor claims)</li></ul><h3>Claim Process Timeline</h3><p>Most insurers process claims within 30 days. Complex claims may take longer. Track your claim status regularly and provide any additional documents promptly.</p><h3>Tips for Smooth Claims</h3><p>Maintain all original documents, never submit falsified information, be transparent about pre-existing conditions, and follow up regularly without being pushy.</p>',

            'mistakes' => '<h2>Common Claim Mistakes and How to Avoid Them</h2><p>Learn from common errors that delay or result in claim rejections.</p><h3>Top 10 Mistakes</h3><ol><li><strong>Late Intimation:</strong> Always inform within the stipulated time frame</li><li><strong>Incomplete Documentation:</strong> Submit all required documents together</li><li><strong>Non-Disclosure:</strong> Declare all material facts truthfully</li><li><strong>Lapsed Policy:</strong> Ensure policy is active with premiums paid</li><li><strong>Exclusion Claims:</strong> Understand what your policy doesn\'t cover</li><li><strong>Unauthorized Repairs:</strong> Get cashless facility or insurer approval first</li><li><strong>Delay in Documentation:</strong> Submit documents as soon as possible</li><li><strong>Incorrect Claim Amount:</strong> Claim realistic amounts with proper bills</li><li><strong>Missing Original Documents:</strong> Keep originals safely, submit copies</li><li><strong>Poor Communication:</strong> Respond to insurer queries promptly</li></ol><h3>Prevention Strategies</h3><p>Read your policy document thoroughly, maintain a claims checklist, use insurer mobile apps for updates, and work with your agent for guidance.</p>',

            'documentation' => '<h2>Claims Documentation Checklist</h2><p>Having the right documents ready speeds up claim processing significantly.</p><h3>Universal Documents (All Claims)</h3><ul><li>Claim form (signed by policyholder)</li><li>Policy document copy</li><li>Identity proof (Aadhaar, PAN, Driving License)</li><li>Address proof</li><li>Cancelled cheque (for reimbursement)</li></ul><h3>Health Insurance Claims</h3><ul><li>Hospitalization bills and receipts</li><li>Discharge summary</li><li>Doctor\'s prescription</li><li>Diagnostic test reports</li><li>Investigation reports</li><li>Pharmacy bills</li></ul><h3>Motor Insurance Claims</h3><ul><li>Driving license copy</li><li>Vehicle RC copy</li><li>FIR copy (if theft/accident)</li><li>Repair estimates/bills</li><li>Photos of damage</li><li>Survey report</li></ul><h3>Digital Document Management</h3><p>Use cloud storage to keep digital copies. Organize by policy and date. Most insurers now accept digital submissions.</p>',
        ];

        return $content[$type] ?? '<p>Comprehensive content about claims ' . $type . '</p>';
    }

    private function getAddonContent($addon)
    {
        return '<h2>Understanding ' . str_replace('-', ' ', ucwords($addon)) . '</h2><p>This add-on provides additional protection beyond your base insurance policy, offering enhanced coverage for specific scenarios.</p><h3>What It Covers</h3><p>Detailed coverage explanation including specific benefits, claim scenarios, and exclusions you should be aware of.</p><h3>Who Should Buy This?</h3><p>This add-on is particularly valuable for customers who want comprehensive protection. We recommend it for specific use cases and customer profiles.</p><h3>Cost vs. Benefit Analysis</h3><p>Understanding the premium impact and potential savings during claims. Real-world scenarios showing when this add-on proves valuable.</p><h3>How to Add to Your Policy</h3><p>You can add this rider at policy inception or during renewal. Contact your insurance agent or use the self-service portal to add coverage.</p><h3>Claim Process</h3><p>Step-by-step guide to filing claims specifically for this add-on, including required documentation and typical settlement timeline.</p>';
    }

    private function getInsuranceTypeContent($type)
    {
        return '<h2>Complete Guide to ' . str_replace('-', ' ', ucwords($type)) . ' Insurance</h2><p>Comprehensive overview of this insurance type, its importance, and how it protects policyholders from financial risks.</p><h3>Key Features</h3><ul><li>Coverage scope and benefits</li><li>Premium calculation factors</li><li>Policy terms and conditions</li><li>Exclusions and limitations</li><li>Optional riders and add-ons</li></ul><h3>Who Needs This Insurance?</h3><p>Target customer profile, risk factors, and scenarios where this insurance becomes essential. Real-life examples of how this insurance provides financial protection.</p><h3>How to Choose the Right Plan</h3><p>Factors to consider when selecting coverage amount, deductibles, premium payment frequency, and term length. Comparing different plans and insurers.</p><h3>Premium Determinants</h3><p>Understanding what affects your premium: age, location, sum insured, past claim history, and other relevant factors specific to this insurance type.</p><h3>Claims and Settlements</h3><p>Typical claim scenarios, required documentation, settlement process, and average turnaround times. Tips for ensuring smooth claim experience.</p><h3>Tax Benefits</h3><p>Tax deductions and benefits available under Section 80C, 80D, and other relevant sections of the Income Tax Act.</p>';
    }
}
