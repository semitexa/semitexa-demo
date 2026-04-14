<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Domain\Model\DemoOrder;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoOrderRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Orm\Transaction\TransactionManager;

#[AsService]
final class DemoDataSeeder
{
    private const TENANTS = ['acme', 'globex', 'initech'];

    private const CATEGORIES = [
        ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Gadgets, devices, and accessories'],
        ['name' => 'Books', 'slug' => 'books', 'description' => 'Fiction, non-fiction, and technical references'],
        ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel, footwear, and accessories'],
        ['name' => 'Home', 'slug' => 'home', 'description' => 'Furniture, décor, and household essentials'],
        ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Equipment, gear, and fitness accessories'],
    ];

    private const PRODUCTS = [
        // Electronics (10)
        ['name' => 'Wireless Headphones', 'price' => 79.99, 'category' => 'electronics', 'description' => 'Noise-cancelling over-ear headphones with 30h battery life.'],
        ['name' => 'USB-C Hub', 'price' => 49.99, 'category' => 'electronics', 'description' => '7-in-1 hub with HDMI, USB-A, SD card, and PD charging.'],
        ['name' => 'Mechanical Keyboard', 'price' => 129.99, 'category' => 'electronics', 'description' => 'Cherry MX Brown switches, RGB backlighting, aluminium frame.'],
        ['name' => 'Portable Monitor', 'price' => 249.99, 'category' => 'electronics', 'description' => '15.6" IPS display, USB-C powered, 1080p.'],
        ['name' => 'Smart Speaker', 'price' => 99.99, 'category' => 'electronics', 'description' => 'Voice-controlled speaker with multi-room audio.'],
        ['name' => 'Webcam Pro', 'price' => 89.99, 'category' => 'electronics', 'description' => '4K webcam with auto-focus and noise-cancelling mic.'],
        ['name' => 'Wireless Mouse', 'price' => 39.99, 'category' => 'electronics', 'description' => 'Ergonomic design, 6 buttons, 12-month battery.'],
        ['name' => 'Power Bank', 'price' => 59.99, 'category' => 'electronics', 'description' => '20000mAh, dual USB-C, fast charging.'],
        ['name' => 'Bluetooth Earbuds', 'price' => 69.99, 'category' => 'electronics', 'description' => 'True wireless, IPX5 waterproof, 24h total play.'],
        ['name' => 'Desk Lamp', 'price' => 44.99, 'category' => 'electronics', 'description' => 'LED desk lamp with wireless charging base.'],
        // Books (10)
        ['name' => 'Clean Architecture', 'price' => 34.99, 'category' => 'books', 'description' => 'Robert C. Martin\'s guide to software structure and design.'],
        ['name' => 'Domain-Driven Design', 'price' => 54.99, 'category' => 'books', 'description' => 'Eric Evans\' seminal work on tackling complexity in software.'],
        ['name' => 'The Pragmatic Programmer', 'price' => 49.99, 'category' => 'books', 'description' => 'Your journey to mastery, 20th anniversary edition.'],
        ['name' => 'PHP 8 in Action', 'price' => 39.99, 'category' => 'books', 'description' => 'Comprehensive guide to modern PHP development.'],
        ['name' => 'Designing Data-Intensive Apps', 'price' => 44.99, 'category' => 'books', 'description' => 'Martin Kleppmann on reliability, scalability, and maintainability.'],
        ['name' => 'Refactoring', 'price' => 49.99, 'category' => 'books', 'description' => 'Improving the design of existing code, 2nd edition.'],
        ['name' => 'Release It!', 'price' => 39.99, 'category' => 'books', 'description' => 'Design and deploy production-ready software.'],
        ['name' => 'System Design Interview', 'price' => 29.99, 'category' => 'books', 'description' => 'An insider\'s guide, volume 1.'],
        ['name' => 'Building Microservices', 'price' => 44.99, 'category' => 'books', 'description' => 'Designing fine-grained systems, 2nd edition.'],
        ['name' => 'Head First Design Patterns', 'price' => 49.99, 'category' => 'books', 'description' => 'A brain-friendly guide to design patterns.'],
        // Clothing (10)
        ['name' => 'Developer Hoodie', 'price' => 59.99, 'category' => 'clothing', 'description' => 'Soft fleece hoodie with "git commit -m sleep" print.'],
        ['name' => 'Tech Conference Tee', 'price' => 24.99, 'category' => 'clothing', 'description' => 'Lightweight cotton tee, available in 5 colours.'],
        ['name' => 'Running Shorts', 'price' => 34.99, 'category' => 'clothing', 'description' => 'Quick-dry fabric, zippered pocket, reflective trim.'],
        ['name' => 'Winter Jacket', 'price' => 149.99, 'category' => 'clothing', 'description' => 'Insulated parka with water-resistant shell.'],
        ['name' => 'Casual Sneakers', 'price' => 89.99, 'category' => 'clothing', 'description' => 'Canvas low-tops, cushioned insole, all-day comfort.'],
        ['name' => 'Wool Beanie', 'price' => 19.99, 'category' => 'clothing', 'description' => 'Merino wool blend, double-layered for warmth.'],
        ['name' => 'Cargo Pants', 'price' => 64.99, 'category' => 'clothing', 'description' => 'Stretch-fit cargo with 6 pockets.'],
        ['name' => 'Linen Shirt', 'price' => 54.99, 'category' => 'clothing', 'description' => 'Breathable linen, relaxed fit, button-down collar.'],
        ['name' => 'Bamboo Socks Pack', 'price' => 14.99, 'category' => 'clothing', 'description' => '5-pack, anti-odour bamboo fibre, arch support.'],
        ['name' => 'Rain Poncho', 'price' => 29.99, 'category' => 'clothing', 'description' => 'Ultralight, packable, waterproof.'],
        // Home (10)
        ['name' => 'Standing Desk', 'price' => 399.99, 'category' => 'home', 'description' => 'Electric sit-stand desk, 120 × 60 cm, memory presets.'],
        ['name' => 'Ergonomic Chair', 'price' => 299.99, 'category' => 'home', 'description' => 'Mesh back, adjustable lumbar, 4D armrests.'],
        ['name' => 'Desk Organiser', 'price' => 29.99, 'category' => 'home', 'description' => 'Bamboo desktop organiser with phone slot.'],
        ['name' => 'Cable Management Kit', 'price' => 19.99, 'category' => 'home', 'description' => 'Under-desk tray, velcro ties, adhesive clips.'],
        ['name' => 'Acoustic Panel Set', 'price' => 89.99, 'category' => 'home', 'description' => '12-pack felt panels, self-adhesive mounting.'],
        ['name' => 'Smart Thermostat', 'price' => 199.99, 'category' => 'home', 'description' => 'Wi-Fi enabled, learning schedule, energy reports.'],
        ['name' => 'Plant Pot Trio', 'price' => 34.99, 'category' => 'home', 'description' => 'Ceramic pots in 3 sizes with drainage trays.'],
        ['name' => 'Whiteboard 90×60', 'price' => 49.99, 'category' => 'home', 'description' => 'Magnetic dry-erase board with marker tray.'],
        ['name' => 'LED Strip Lights', 'price' => 24.99, 'category' => 'home', 'description' => '5m RGB strip, app-controlled, music sync.'],
        ['name' => 'Air Purifier', 'price' => 149.99, 'category' => 'home', 'description' => 'HEPA filter, 3 fan speeds, night mode.'],
        // Sports (10)
        ['name' => 'Yoga Mat', 'price' => 39.99, 'category' => 'sports', 'description' => '6mm thick, non-slip, carrying strap included.'],
        ['name' => 'Resistance Band Set', 'price' => 24.99, 'category' => 'sports', 'description' => '5 bands, door anchor, ankle straps, carry bag.'],
        ['name' => 'Jump Rope', 'price' => 14.99, 'category' => 'sports', 'description' => 'Speed rope, adjustable length, ball bearings.'],
        ['name' => 'Foam Roller', 'price' => 29.99, 'category' => 'sports', 'description' => 'High-density EVA foam, 45 cm, textured surface.'],
        ['name' => 'Water Bottle 1L', 'price' => 19.99, 'category' => 'sports', 'description' => 'Stainless steel, double-wall insulated, leak-proof.'],
        ['name' => 'Dumbbell Pair 10kg', 'price' => 59.99, 'category' => 'sports', 'description' => 'Neoprene-coated, hex design, anti-roll.'],
        ['name' => 'Running Vest', 'price' => 44.99, 'category' => 'sports', 'description' => 'Lightweight mesh, reflective strips, phone pocket.'],
        ['name' => 'Pull-Up Bar', 'price' => 34.99, 'category' => 'sports', 'description' => 'Doorframe mount, padded grips, up to 150 kg.'],
        ['name' => 'Tennis Racket', 'price' => 79.99, 'category' => 'sports', 'description' => 'Graphite frame, pre-strung, head-light balance.'],
        ['name' => 'Cycling Gloves', 'price' => 22.99, 'category' => 'sports', 'description' => 'Gel-padded, half-finger, breathable mesh.'],
    ];

    private const REVIEW_BODIES = [
        'Excellent quality, exactly as described.',
        'Good value for the price. Would buy again.',
        'Arrived quickly and well packaged.',
        'Decent product but could be improved.',
        'Not what I expected. Returning it.',
        'Perfect for my needs. Highly recommended!',
        'Solid build quality. Very satisfied.',
        'Works as advertised. No complaints.',
        'A bit overpriced for what you get.',
        'Great gift idea. My friend loved it.',
        'The best purchase I\'ve made this year.',
        'Average quality. Nothing special.',
        'Broke after a week. Disappointing.',
        'Superb craftsmanship and attention to detail.',
        'Functional but not particularly stylish.',
        'Five stars. Will be ordering more.',
        'Meets expectations. Fair price.',
        'Shipping was slow but product is good.',
        'Better than the competitor\'s version.',
        'Comfortable and practical. Love it.',
    ];

    private const ORDER_STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    #[InjectAsReadonly]
    protected ?DemoCategoryRepositoryInterface $categoryRepository = null;

    #[InjectAsReadonly]
    protected ?DemoProductRepositoryInterface $productRepository = null;

    #[InjectAsReadonly]
    protected ?DemoReviewRepositoryInterface $reviewRepository = null;

    #[InjectAsReadonly]
    protected ?DemoOrderRepositoryInterface $orderRepository = null;

    #[InjectAsReadonly]
    protected ?DemoJobRunRepositoryInterface $jobRunRepository = null;

    #[InjectAsReadonly]
    protected ?DemoAiTaskRepositoryInterface $aiTaskRepository = null;

    #[InjectAsReadonly]
    protected ?TransactionManager $transactionManager = null;

    /**
     * Seed all demo data. Returns summary counts.
     *
     * @return array<string, int>
     */
    public function seed(): array
    {
        $seed = function (): array {
            $counts = [
                'categories' => 0,
                'products' => 0,
                'reviews' => 0,
                'orders' => 0,
                'job_runs' => 0,
                'ai_tasks' => 0,
            ];

            $categoryIds = $this->seedCategories();
            $counts['categories'] = count($categoryIds);

            $productIds = $this->seedProducts($categoryIds);
            $counts['products'] = count($productIds);

            $counts['reviews'] = $this->seedReviews($productIds);
            $counts['orders'] = $this->seedOrders();
            $counts['job_runs'] = $this->seedJobRuns();
            $counts['ai_tasks'] = $this->seedAiTasks();

            return $counts;
        };

        if ($this->transactionManager === null) {
            return $seed();
        }

        /** @var array<string, int> $counts */
        $counts = $this->transactionManager->run(static fn () => $seed());

        return $counts;
    }

    /**
     * Check if demo data has already been seeded.
     */
    public function isSeeded(): bool
    {
        return $this->categoryRepository->findBySlug('electronics') !== null
            && $this->productRepository->findByTenant('acme', 1) !== []
            && $this->reviewRepository->findAll(1) !== []
            && $this->orderRepository->findAll(1) !== []
            && $this->jobRunRepository->findByJobType('report_generation') !== []
            && $this->aiTaskRepository->findByStatus('completed') !== [];
    }

    /**
     * @return array<string, string> slug → id
     */
    private function seedCategories(): array
    {
        $map = [];

        foreach (self::CATEGORIES as $data) {
            $category = new DemoCategory();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);

            $category = $this->categoryRepository->save($category);
            $map[$data['slug']] = $category->getId();
        }

        return $map;
    }

    /**
     * @param array<string, string> $categoryIds slug → id
     * @return list<string> product IDs
     */
    private function seedProducts(array $categoryIds): array
    {
        $productIds = [];
        $tenants = self::TENANTS;
        $tenantCount = count($tenants);

        foreach (self::PRODUCTS as $i => $data) {
            $product = new DemoProduct();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice(number_format($data['price'], 2, '.', ''));
            $product->setStatus('active');
            $product->setCategoryId($categoryIds[$data['category']] ?? null);
            $product->setTenantId($tenants[$i % $tenantCount]);

            $product = $this->productRepository->save($product);
            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    /**
     * @param list<string> $productIds
     */
    private function seedReviews(array $productIds): int
    {
        $count = 0;
        $bodies = self::REVIEW_BODIES;
        $bodyCount = count($bodies);
        $productCount = count($productIds);
        $tenants = self::TENANTS;
        $tenantCount = count($tenants);

        for ($i = 0; $i < 200; $i++) {
            $review = new DemoReview();
            $review->setProductId($productIds[$i % $productCount]);
            $review->setUserId(sprintf('demo-user-%03d', ($i % 20) + 1));
            $review->setRating(($i % 5) + 1);
            $review->setBody($bodies[$i % $bodyCount]);
            $review->setTenantId($tenants[$i % $tenantCount]);

            $this->reviewRepository->save($review);
            $count++;
        }

        return $count;
    }

    private function seedOrders(): int
    {
        $count = 0;
        $statuses = self::ORDER_STATUSES;
        $statusCount = count($statuses);
        $tenants = self::TENANTS;
        $tenantCount = count($tenants);

        for ($i = 0; $i < 20; $i++) {
            $order = new DemoOrder();
            $order->setUserId(sprintf('demo-user-%03d', ($i % 10) + 1));
            $order->setStatus($statuses[$i % $statusCount]);
            $order->setTotalAmount(number_format(19.99 + ($i * 15.50), 2, '.', ''));
            $order->setTenantId($tenants[$i % $tenantCount]);

            $this->orderRepository->save($order);
            $count++;
        }

        return $count;
    }

    private function seedJobRuns(): int
    {
        $jobTypes = ['report_generation', 'product_import', 'analytics_aggregation', 'ai_processing'];
        $statuses = ['pending', 'running', 'completed', 'failed'];

        $count = 0;
        foreach ($jobTypes as $i => $type) {
            $run = new DemoJobRun();
            $run->setJobType($type);
            $run->setStatus($statuses[$i]);
            $run->setProgressPercent(match ($statuses[$i]) {
                'completed' => 100,
                'running' => 42,
                'failed' => 67,
                default => 0,
            });
            $run->setProgressMessage(match ($statuses[$i]) {
                'completed' => 'Finished successfully.',
                'running' => 'Processing batch 3 of 7…',
                'failed' => 'Connection timeout after 3 retries.',
                default => null,
            });
            $run->setAttemptNumber($statuses[$i] === 'failed' ? 3 : 1);

            $this->jobRunRepository->save($run);
            $count++;
        }

        return $count;
    }

    private function seedAiTasks(): int
    {
        $tasks = [
            ['input' => 'Summarise the top 5 products by revenue.', 'status' => 'completed'],
            ['input' => 'Generate product descriptions for the Sports category.', 'status' => 'running'],
            ['input' => 'Classify reviews by sentiment.', 'status' => 'pending'],
        ];
        $tenants = self::TENANTS;

        $count = 0;
        foreach ($tasks as $i => $data) {
            $task = new DemoAiTask();
            $task->setInputText($data['input']);
            $task->setStatus($data['status']);
            $task->setTenantId($tenants[$i % count($tenants)]);
            $task->setStages(json_encode(['parse', 'process', 'format'], JSON_THROW_ON_ERROR));
            if ($data['status'] === 'completed') {
                $task->setStageResults(json_encode([
                    'parse' => ['status' => 'done', 'tokens' => 42],
                    'process' => ['status' => 'done', 'tokens' => 156],
                    'format' => ['status' => 'done', 'tokens' => 23],
                ], JSON_THROW_ON_ERROR));
            }

            $this->aiTaskRepository->save($task);
            $count++;
        }

        return $count;
    }
}
