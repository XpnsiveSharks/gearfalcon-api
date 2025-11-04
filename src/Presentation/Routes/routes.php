<?php

use App\Presentation\Controllers\Technician\TechnicianController;
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\Customer\QuoteController;
use App\Presentation\Controllers\Customer\CustomerController;
use App\Presentation\Controllers\JobController;
use App\Presentation\Controllers\PaymentController;
use App\Presentation\Controllers\Customer\CartController;
use App\Presentation\Controllers\Admin\UserController;
use App\Presentation\Controllers\Admin\AdminController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // Test home route
    $r->addRoute('GET', '/', function() {
        echo "Welcome to GearFalcon API 🚀";
    });
    
    // Health check route
    $r->addRoute('GET', '/health', function() {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'environment' => getenv('APP_ENV') ?: 'development'
        ]);
    });
    
    // Auth routes
    $r->addGroup('/auth', function (RouteCollector $r) {
        $r->addRoute('POST', '/login', [AuthController::class, 'login']);
        $r->addRoute('POST', '/register', [AuthController::class, 'register']);
        $r->addRoute('POST', '/refresh', [AuthController::class, 'refresh']);
        $r->addRoute('POST', '/logout', [AuthController::class, 'logout']);
        $r->addRoute('POST', '/verify-email', [AuthController::class, 'verifyEmail']);
        $r->addRoute('POST', '/resend-verification', [AuthController::class, 'resendVerificationCode']);
        $r->addRoute('GET', '/customer-info', [AuthController::class, 'getCustomerInfo']);
        $r->addRoute('POST', '/forgot-password', [AuthController::class, 'forgotPassword']);
        $r->addRoute('POST', '/verify-password-reset', [AuthController::class, 'verifyPasswordReset']);
        $r->addRoute('POST', '/reset-password', [AuthController::class, 'resetPassword']);
        $r->addRoute('PUT', '/user/update', [AuthController::class, 'updateProfile']);
    });
    
    $r->addGroup('/webhooks', function (RouteCollector $r) {
        $r->addRoute('POST', '/paymongo', [PaymentController::class, 'handleWebhook']);
    });
    
   
    // Customer quotes
    $r->addRoute('GET', '/customers/{id:\d+}/quotes', [QuoteController::class, 'getByCustomer']);
    
    
    
    // Customer profile routes
    $r->addGroup('/customers', function (RouteCollector $r) {
        // Complete customer profile
        $r->addRoute('POST', '/complete-profile', [CustomerController::class, 'completeProfile']);
        $r->addRoute('PUT', '/change-password/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [CustomerController::class, 'changePassword']);
        $r->addRoute('PUT', '/change-address/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [CustomerController::class, 'changeAddress']); 
        $r->addRoute('PUT', '/change-email/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AuthController::class, 'changeEmail']); 
        
        //Cart Items
        $r->addGroup('/cart', function (RouteCollector $r) {
            $r->addRoute('GET', '', [CartController::class, 'getCartItems']);
            $r->addRoute('GET', 'getItemsByCartId', [CartController::class, 'getCartItems']);
            $r->addRoute('POST', '/items', [CartController::class, 'addToCart']);
            $r->addRoute('DELETE', '/items/{id:\d+}', [CartController::class, 'removeFromCart']);
            $r->addRoute('PUT', '/items/{id:\d+}', [CartController::class, 'updateCartItem']);
            $r->addRoute('DELETE', '', [CartController::class, 'clearCart']);
            $r->addRoute('PUSH', '', [CartController::class, 'clearCart']); 
            $r->addRoute('POST', '/checkout', [PaymentController::class, 'createPaymentSource']);
        });
        
        //Customer Jobs
        $r->addGroup('/jobs', function (RouteCollector $r) {
            $r->addRoute('POST', '', [JobController::class, 'createJob']); // create a job
            $r->addRoute('GET', '/{id:\d+}', [JobController::class, 'getJobsByCustomer']); // get jobs by customer
            $r->addRoute('GET', '/{id:\d+}/technician', [JobController::class, 'getTechnicianForJob']); // get technician for a job
            $r->addRoute('PUT', '/{id:\d+}/cancel', [JobController::class, 'cancelJob']);
            $r->addRoute('PUT', '/{id:\d+}/complete', [JobController::class, 'completeJob']); // Mark job as completed by customer
        });
    });

    // Public catalog routes
    $r->addGroup('/catalog', function (RouteCollector $r) {
        // $r->addRoute('GET', '/categories', [CatalogController::class, 'getCategories']);
        $r->addRoute('GET', '/services', [AdminController::class, 'listServices']);            // list services
        $r->addRoute('GET', '/categories', [AdminController::class, 'index']);            // list categories
    });

    // Technician routes
    $r->addGroup('/technicians', function (RouteCollector $r) {
        $r->addRoute('PUT','/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}',[TechnicianController::class, 'updateTechnician']);
        $r->addRoute('POST', '/jobs/{id:\d+}/claim', [JobController::class, 'claimJob']);
        $r->addRoute('GET', '/jobs/assigned', [JobController::class, 'assignedJobs']); // New route for assigned jobs
        $r->addRoute('GET', '/jobs/service-history', [JobController::class, 'serviceHistory']);
    });



    // Admin routes
    $r->addGroup('/admin', function (RouteCollector $r) {
        // Customer routes
        $r->addGroup('/users', function (RouteCollector $r) {   
        $r->addRoute('POST', '', [AdminController::class, 'makeUser']);
        $r->addRoute('GET', '', [AdminController::class, 'listUsers']);
        $r->addRoute('DELETE', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'deleteUser']);
        $r->addRoute('PUT', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'updateUser']);
        });

         // Quote routes  
        $r->addGroup('/quotes', function (RouteCollector $r) {
            $r->addRoute('POST', '', [QuoteController::class, 'create']);                 // create a quote
            $r->addRoute('POST', '/{id:\d+}/accept', [QuoteController::class, 'accept']); // accept a quote
            $r->addRoute('POST', '/{id:\d+}/reject', [QuoteController::class, 'reject']); // reject a quote
            $r->addRoute('GET', '/active', [QuoteController::class, 'getActive']);        // list all active quotes
        });

        $r->addGroup('/customers', function (RouteCollector $r){
            $r->addRoute('GET', '', [AdminController::class, 'listCustomers']);
            $r->addRoute('GET', '/address', [AdminController::class, 'listCustomerAddresses']);
            $r->addRoute('GET', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'getCustomerDetails']);
            $r->addRoute('PUT', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'updateCustomer']);
            $r->addRoute('PUT', '/carts', [CartController::class, 'changeStatus']);
        });


        // Technician routes
        $r->addGroup('/technicians', function (RouteCollector $r) {
            $r->addRoute('PUT', '/promote/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [UserController::class, 'promote']);
            $r->addRoute('DELETE','/demote/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}',[UserController::class, 'demote']);
            $r->addRoute('GET', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'getTechnicianDetails']);
            $r->addRoute('PUT', '/{id:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}}', [AdminController::class, 'updateTechnician']);
            $r->addRoute('GET', '', [AdminController::class, 'listTechnicians']);
        });
        
        // Admin-only routes to manage technician skills
        
        // Admin-only routes to manage master skills list
        $r->addGroup('/skills', function (RouteCollector $r) {
            $r->addRoute('POST', '', [AdminController::class, 'createSkill']); // Create a new skill
            $r->addRoute('GET', '', [AdminController::class, 'listSkills']);   // List all skills
            $r->addRoute('PUT', '/{id:\d+}', [AdminController::class, 'updateSkill']); // Update a skill
            $r->addRoute('DELETE', '/{id:\d+}', [AdminController::class, 'deleteSkill']); // Delete a skill
        });

        // Service Category routes
        $r->addGroup('/categories', function (RouteCollector $r) {
            $r->addRoute('GET', '', [AdminController::class, 'index']);            // list categories
            $r->addRoute('POST', '', [AdminController::class, 'store']);           // create category
            $r->addRoute('PUT', '/{id:\d+}', [AdminController::class, 'update']);  // update category
            $r->addRoute('DELETE', '/{id:\d+}', [AdminController::class, 'destroy']); // soft delete category
        });

        $r->addGroup('/services', function (RouteCollector $r) {
            $r->addRoute('GET', '', [AdminController::class, 'listServices']);            // list services
            $r->addRoute('POST', '', [AdminController::class, 'createService']);          // create service
            $r->addRoute('PUT', '/{id:\d+}', [AdminController::class, 'updateService']);  // update service
            $r->addRoute('DELETE', '/{id:\d+}', [AdminController::class, 'deleteService']); // soft delete service
        });

        // Admin Job Assignment routes
        $r->addGroup('/jobs', function (RouteCollector $r) {
            $r->addRoute('GET', '/available', [JobController::class, 'getAvailableJobs']); // New route for available jobs
            $r->addRoute('GET', '/emergency', [JobController::class, 'getEmergencyJobs']); // list emergency jobs
            $r->addRoute('GET', '/taken', [JobController::class, 'TakenJobs']); // New route for available jobs
            $r->addRoute('GET', '/cancelled', [JobController::class, 'getCancelledJobs']); // Get all cancelled jobs
            $r->addRoute('PUT', '/{job_id:\d+}/assign/{technician_id:\d+}', [JobController::class, 'assignJob']); // assign job to technician
            $r->addRoute('GET', '/refunded', [JobController::class, 'getRefunded']); // Get all refunded jobs
            $r->addRoute('POST', '/refund', [JobController::class, 'refund']);
            $r->addRoute('PUT', '/{job_id:\d+}/unassign', [JobController::class, 'unassignJob']); // unassign job from technician
        });
    });
};