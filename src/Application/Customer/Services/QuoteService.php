<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\QuoteRepository;
use App\Infrastructure\Repositories\JobRepository;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * QuoteService manages quote-related tasks, such as creating quotes, accepting or rejecting them,
 * and fetching quotes for customers or active ones. It works with QuoteRepository and JobRepository
 * to handle data using standalone Eloquent. When a customer accepts a quote, it creates a job,
 * which can then be assigned to a technician and scheduled for service.
 */
class QuoteService
{
    /**
     * @var QuoteRepository $quoteRepository Repository for managing quote data.
     */
    private QuoteRepository $quoteRepository;

    /**
     * @var JobRepository $jobRepository Repository for managing job data.
     */
    private JobRepository $jobRepository;

    /**
     * Constructor to set up the service with repositories for quotes and jobs.
     *
     * @param QuoteRepository $quoteRepository Handles quote database operations.
     * @param JobRepository $jobRepository Handles job database operations.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        JobRepository $jobRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->jobRepository = $jobRepository;
    }

    /**
     * Creates a new quote with the provided data.
     *
     * @param array $data Data for the new quote (e.g., customer ID, service ID).
     * @return mixed The created quote record.
     */
    public function createQuote(array $data)
    {
        // Save the new quote using the QuoteRepository
        return $this->quoteRepository->create($data);
    }

    /**
     * Accepts a quote and creates a job from it.
     * Part of the workflow: Customer accepts quote → Job is created → Technician is assigned → Service is scheduled.
     *
     * @param int $quoteId The ID of the quote to accept.
     * @return mixed The created job record.
     * @throws Exception If the quote doesn't exist or isn't pending.
     */
    public function acceptQuote(int $quoteId)
    {
        // Use a database transaction to keep data safe
        return DB::transaction(function () use ($quoteId) {
            // Find the quote with its details
            $quote = $this->quoteRepository->findWithDetails($quoteId);

            // Check if the quote exists
            if (!$quote) {
                throw new Exception("Quote not found.");
            }

            // Check if the quote is pending
            if ($quote->status !== 'pending') {
                throw new Exception("Only pending quotes can be accepted.");
            }

            // Mark the quote as accepted
            $this->quoteRepository->acceptQuote($quoteId);

            // Create a job based on the quote's data
            $jobData = [
                'customer_id'         => $quote->customer_id,
                'customer_address_id' => $quote->customer_address_id,
                'service_id'          => $quote->service_id,
                'cart_id'             => $quote->cart_id,
                'status'              => 'pending',
                'scheduled_date'      => $quote->valid_until, // Use quote's valid date for scheduling
                'notes'               => "Job created from quote #{$quote->id}",
            ];

            // Create and return the new job
            return $this->jobRepository->create($jobData);
        });
    }

    /**
     * Rejects a quote.
     *
     * @param int $quoteId The ID of the quote to reject.
     * @return bool True if the quote was rejected, false otherwise.
     */
    public function rejectQuote(int $quoteId): bool
    {
        // Update the quote's status to rejected using the QuoteRepository
        return $this->quoteRepository->rejectQuote($quoteId);
    }

    /**
     * Gets all quotes for a specific customer.
     *
     * @param int $customerId The ID of the customer to find quotes for.
     * @return mixed A list of quotes for the customer.
     */
    public function getQuotesByCustomer(int $customerId)
    {
        // Fetch quotes for the customer using the QuoteRepository
        return $this->quoteRepository->findByCustomerId($customerId);
    }

    /**
     * Gets all active (pending) quotes.
     *
     * @return mixed A list of active quotes.
     */
    public function getActiveQuotes()
    {
        // Fetch active quotes using the QuoteRepository
        return $this->quoteRepository->findActive();
    }
}