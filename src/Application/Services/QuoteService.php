<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\QuoteRepository;
use App\Infrastructure\Repositories\JobRepository;
use Illuminate\Support\Carbon;

class QuoteService
{
    private QuoteRepository $quoteRepository;
    private JobRepository $jobRepository;

    public function __construct(QuoteRepository $quoteRepository, JobRepository $jobRepository)
    {
        $this->quoteRepository = $quoteRepository;
        $this->jobRepository = $jobRepository;
    }

    /**
     * Create a new quote.
     */
    public function createQuote(array $request)
    {
        $data = [
            'customer_id' => $request['customer_id'],
            'customer_address_id' => $request['customer_address_id'] ?? null,
            'cart_id' => $request['cart_id'] ?? null,
            'total_amount' => $request['total_amount'],
            'status' => 'pending',
            'valid_until' => $request['valid_until'] ?? Carbon::now()->addDays(7),
        ];

        return $this->quoteRepository->create($data);
    }

    /**
     * Accept a quote and create a job.
     */
    public function acceptQuote(int $quoteId)
    {
        $quote = $this->quoteRepository->findWithDetails($quoteId);

        if (!$quote) {
            throw new \Exception('Quote not found');
        }

        // Accept the quote
        $this->quoteRepository->acceptQuote($quoteId);

        // Create job from quote
        $jobData = [
            'customer_id' => $quote->customer_id,
            'customer_address_id' => $quote->customer_address_id,
            'cart_id' => $quote->cart_id,
            'status' => 'pending',
            'scheduled_date' => Carbon::now()->addDays(1), // Default to tomorrow
        ];

        $job = $this->jobRepository->create($jobData);

        return $job;
    }

    /**
     * Reject a quote.
     */
    public function rejectQuote(int $quoteId): bool
    {
        return $this->quoteRepository->rejectQuote($quoteId);
    }

    /**
     * Get quotes by customer ID.
     */
    public function getQuotesByCustomer(int $customerId)
    {
        return $this->quoteRepository->findByCustomerId($customerId);
    }

    /**
     * Get all active quotes.
     */
    public function getActiveQuotes()
    {
        return $this->quoteRepository->findActive();
    }
}