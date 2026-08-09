<?php

namespace App\Services;

use App\Models\PopupTemplate;

class PopupTemplateService
{
    public const POPUP_EVENTS = [
        // Error Popups
        'error_login_failed' => [
            'label' => 'Error: Login Failed',
            'category' => 'errors',
            'heading' => 'Login Failed',
            'subtitle' => 'Authentication Error',
            'body_text' => "We could not log you in with the provided credentials. Please double check your email and password.",
            'button_text' => 'Try Again',
            'button_url' => '/login',
            'variables' => ['error_message'],
        ],
        'error_registration_failed' => [
            'label' => 'Error: Registration Failed',
            'category' => 'errors',
            'heading' => 'Registration Failed',
            'subtitle' => 'Account Creation Error',
            'body_text' => "Unable to complete account registration. {{error_message}}",
            'button_text' => 'Back to Registration',
            'button_url' => '/register',
            'variables' => ['error_message'],
        ],
        'error_checkout_failed' => [
            'label' => 'Error: Checkout Failed',
            'category' => 'errors',
            'heading' => 'Payment Could Not Be Processed',
            'subtitle' => 'Checkout Error',
            'body_text' => "We were unable to complete your order payment. {{error_message}}\n\nPlease review your payment information and try again.",
            'button_text' => 'Return to Checkout',
            'button_url' => '/checkout',
            'variables' => ['error_message', 'order_number'],
        ],
        'error_unauthorized' => [
            'label' => 'Error: Access Denied',
            'category' => 'errors',
            'heading' => 'Access Denied',
            'subtitle' => 'Session Expired or Unauthorized',
            'body_text' => "You do not have permission to perform this action or your login session has expired. Please sign in to continue.",
            'button_text' => 'Sign In',
            'button_url' => '/login',
            'variables' => ['error_message'],
        ],
        'error_network_failure' => [
            'label' => 'Error: Connection Failure',
            'category' => 'errors',
            'heading' => 'Network Error',
            'subtitle' => 'Server Communication Issue',
            'body_text' => "We couldn't connect to our servers. Please check your internet connection and try refreshing.",
            'button_text' => 'Retry',
            'button_url' => '#',
            'variables' => ['error_message'],
        ],
        'error_validation_failed' => [
            'label' => 'Error: Validation Failed',
            'category' => 'errors',
            'heading' => 'Please Correct Form Errors',
            'subtitle' => 'Invalid Inputs',
            'body_text' => "Some information you entered is invalid or missing. {{error_message}}",
            'button_text' => 'Fix Inputs',
            'button_url' => '#',
            'variables' => ['error_message'],
        ],
        'coupon_invalid' => [
            'label' => 'Error: Coupon Invalid',
            'category' => 'errors',
            'heading' => 'Invalid Coupon Code',
            'subtitle' => 'Coupon Cannot Be Applied',
            'body_text' => "The coupon code {{coupon_code}} is invalid, expired, or does not apply to the items in your cart.",
            'button_text' => 'Try Another Code',
            'button_url' => '/cart',
            'variables' => ['coupon_code', 'error_message'],
        ],
        'insufficient_balance' => [
            'label' => 'Error: Insufficient Wallet Balance',
            'category' => 'errors',
            'heading' => 'Insufficient Wallet Funds',
            'subtitle' => 'Payment Required',
            'body_text' => "Your current wallet balance ({{current_balance}}) is insufficient to complete this payment of {{total_amount}}.",
            'button_text' => 'Deposit Funds',
            'button_url' => '/account/wallet',
            'variables' => ['current_balance', 'total_amount'],
        ],
        'error_generic' => [
            'label' => 'Error: General Failure',
            'category' => 'errors',
            'heading' => 'Something Went Wrong',
            'subtitle' => 'Unexpected Issue',
            'body_text' => "An unexpected error occurred: {{error_message}}. Please try again or contact support if the issue persists.",
            'button_text' => 'Dismiss',
            'button_url' => '#',
            'variables' => ['error_message'],
        ],

        // Orders & Checkout Events
        'order_success' => [
            'label' => 'Order Success',
            'category' => 'orders',
            'heading' => 'Order Confirmed! 🎉',
            'subtitle' => 'Thank you for your purchase',
            'body_text' => "Your order {{order_number}} has been placed successfully.\n\nTotal: {{total_amount}}\n\nWe'll send you updates on your order status.",
            'button_text' => 'View My Orders',
            'button_url' => '/account/orders',
            'variables' => ['customer_name', 'order_number', 'total_amount'],
        ],
        'order_cancelled' => [
            'label' => 'Order Cancelled',
            'category' => 'orders',
            'heading' => 'Order Cancelled',
            'subtitle' => 'Order Status Update',
            'body_text' => "Order {{order_number}} has been cancelled.",
            'button_text' => 'View Orders',
            'button_url' => '/account/orders',
            'variables' => ['order_number', 'reason'],
        ],
        'quote_submitted' => [
            'label' => 'Quote Request Submitted',
            'category' => 'sales',
            'heading' => 'Quote Request Received! 📋',
            'subtitle' => 'Our team will review your specs',
            'body_text' => "Thank you {{customer_name}}! Your quote request {{quote_number}} has been received. Estimated total: {{total_amount}}.\n\nOur embroidery specialists will get back to you shortly.",
            'button_text' => 'View Quotes',
            'button_url' => '/account/quotes',
            'variables' => ['customer_name', 'quote_number', 'total_amount'],
        ],
        'cart_reminder' => [
            'label' => 'Cart Reminder',
            'category' => 'sales',
            'heading' => 'You Left Something Behind!',
            'subtitle' => 'Your cart is waiting for you',
            'body_text' => "Hi {{customer_name}},\n\nYou have {{cart_items_count}} item(s) in your cart. Complete your purchase before they're gone!",
            'button_text' => 'View Cart',
            'button_url' => '/cart',
            'variables' => ['customer_name', 'cart_items_count'],
        ],
        'coupon_applied' => [
            'label' => 'Coupon Applied',
            'category' => 'sales',
            'heading' => 'Discount Applied! 🎉',
            'subtitle' => 'You saved on your order',
            'body_text' => "Coupon code {{coupon_code}} has been applied.\nYou save {{discount_amount}} on this order!",
            'button_text' => 'Continue Shopping',
            'button_url' => '/shop',
            'variables' => ['coupon_code', 'discount_amount'],
        ],

        // User & Security Events
        'welcome_new_user' => [
            'label' => 'Welcome New User',
            'category' => 'onboarding',
            'heading' => 'Welcome to Mecarvi!',
            'subtitle' => 'We are glad to have you',
            'body_text' => "Hi {{customer_name}},\n\nWelcome to Mecarvi Embroidery! Explore our wide range of products and find exactly what you need.",
            'button_text' => 'Start Shopping',
            'button_url' => '/shop',
            'variables' => ['customer_name', 'customer_email'],
        ],
        'account_verified' => [
            'label' => 'Account Verified',
            'category' => 'system',
            'heading' => 'Account Verified ✓',
            'subtitle' => 'You are all set',
            'body_text' => "Hi {{customer_name}},\n\nYour account has been successfully verified. You now have full access to all features.",
            'button_text' => 'Go to Dashboard',
            'button_url' => '/account',
            'variables' => ['customer_name'],
        ],
        'password_changed' => [
            'label' => 'Password Updated',
            'category' => 'security',
            'heading' => 'Password Updated Successfully 🔒',
            'subtitle' => 'Security Update',
            'body_text' => "Hi {{customer_name}},\n\nYour account password has been updated successfully.",
            'button_text' => 'My Account',
            'button_url' => '/account',
            'variables' => ['customer_name'],
        ],
        'email_changed' => [
            'label' => 'Email Updated',
            'category' => 'security',
            'heading' => 'Email Updated ✉️',
            'subtitle' => 'Profile Updated',
            'body_text' => "Your account email address has been changed to {{new_email}}.",
            'button_text' => 'My Account',
            'button_url' => '/account',
            'variables' => ['new_email'],
        ],
        'pin_updated' => [
            'label' => 'PIN Code Updated',
            'category' => 'security',
            'heading' => 'Security PIN Set 🔐',
            'subtitle' => 'Security Update',
            'body_text' => "Your 4-digit security PIN has been updated successfully.",
            'button_text' => 'Account Security',
            'button_url' => '/account/security',
            'variables' => ['customer_name'],
        ],

        // Wallet & Financial Events
        'wallet_deposit_success' => [
            'label' => 'Wallet Deposit Success',
            'category' => 'wallet',
            'heading' => 'Wallet Credited 💳',
            'subtitle' => 'Funds Added Successfully',
            'body_text' => "An amount of {{amount}} has been credited to your wallet. Your new balance is {{new_balance}}.",
            'button_text' => 'View Wallet',
            'button_url' => '/account/wallet',
            'variables' => ['amount', 'new_balance'],
        ],
        'wallet_debit_success' => [
            'label' => 'Wallet Payment Success',
            'category' => 'wallet',
            'heading' => 'Wallet Payment Processed 💳',
            'subtitle' => 'Transaction Completed',
            'body_text' => "An amount of {{amount}} was deducted from your wallet balance. Remaining balance: {{new_balance}}.",
            'button_text' => 'View Wallet',
            'button_url' => '/account/wallet',
            'variables' => ['amount', 'new_balance'],
        ],
        'payout_requested' => [
            'label' => 'Payout Requested',
            'category' => 'wallet',
            'heading' => 'Payout Request Submitted 💰',
            'subtitle' => 'Under Review',
            'body_text' => "Your payout request for {{amount}} has been submitted and is under review.",
            'button_text' => 'View Payouts',
            'button_url' => '/account/affiliate',
            'variables' => ['amount', 'payout_id'],
        ],
        'referral_commission_earned' => [
            'label' => 'Referral Commission Earned',
            'category' => 'rewards',
            'heading' => 'Commission Earned! 🎁',
            'subtitle' => 'Affiliate Reward',
            'body_text' => "Congratulations {{customer_name}}! You earned {{commission_amount}} in referral commission.",
            'button_text' => 'View Earnings',
            'button_url' => '/account/affiliate',
            'variables' => ['customer_name', 'commission_amount'],
        ],

        // Support & Inquiries
        'question_submitted' => [
            'label' => 'Product Question Submitted',
            'category' => 'support',
            'heading' => 'Question Received ❓',
            'subtitle' => 'We will respond shortly',
            'body_text' => "Thank you! Your question regarding {{product_name}} has been submitted. We will notify you when an answer is posted.",
            'button_text' => 'View Product',
            'button_url' => '/shop',
            'variables' => ['product_name'],
        ],
        'ticket_created' => [
            'label' => 'Support Ticket Submitted',
            'category' => 'support',
            'heading' => 'Support Ticket Created 🎫',
            'subtitle' => 'Ticket #{{ticket_id}}',
            'body_text' => "Your support request has been registered. Our team will review your inquiry and respond soon.",
            'button_text' => 'View Ticket',
            'button_url' => '/account/support',
            'variables' => ['ticket_id', 'subject'],
        ],

        // Marketing & Promotions
        'newsletter_signup' => [
            'label' => 'Newsletter Signup',
            'category' => 'onboarding',
            'heading' => 'Thanks for Subscribing!',
            'subtitle' => 'Stay updated with our latest news',
            'body_text' => "You've successfully subscribed to our newsletter. Get ready for exclusive offers, new arrivals, and more!",
            'button_text' => 'Browse Products',
            'button_url' => '/shop',
            'variables' => ['customer_email'],
        ],
        'review_request' => [
            'label' => 'Review Request',
            'category' => 'orders',
            'heading' => 'How Was Your Order?',
            'subtitle' => 'We would love your feedback',
            'body_text' => "Hi {{customer_name}},\n\nYour order {{order_number}} has been delivered. We'd love to hear your thoughts! Leave a review and help other shoppers.",
            'button_text' => 'Write a Review',
            'button_url' => '/account/orders',
            'variables' => ['customer_name', 'order_number'],
        ],
        'promotion_banner' => [
            'label' => 'Promotion Banner',
            'category' => 'promotions',
            'heading' => 'Special Offer!',
            'subtitle' => 'Limited time deal',
            'body_text' => "Don't miss out on our special promotion! {{promotion_details}}",
            'button_text' => 'Shop Now',
            'button_url' => '/shop',
            'variables' => ['promotion_details', 'discount_amount'],
        ],
    ];

    public function ensureDefaultTemplates(): void
    {
        foreach (self::POPUP_EVENTS as $eventKey => $definition) {
            PopupTemplate::firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'name' => $definition['label'],
                    'event_key' => $eventKey,
                    'category' => $definition['category'],
                    'heading' => $definition['heading'],
                    'subtitle' => $definition['subtitle'] ?? null,
                    'body_text' => $definition['body_text'],
                    'body_html' => '<p>' . nl2br(e($definition['body_text'])) . '</p>',
                    'button_text' => $definition['button_text'] ?? null,
                    'button_url' => $definition['button_url'] ?? null,
                    'footer_text' => 'Mecarvi Embroidery',
                    'status' => 'published',
                    'variables' => $definition['variables'],
                    'popup_size' => 'medium',
                    'popup_position' => 'center',
                    'overlay_opacity' => 60,
                    'show_close_button' => true,
                    'button_style' => 'primary',
                    'trigger_type' => 'event',
                ]
            );
        }
    }

    public function restoreTemplate(PopupTemplate $template): PopupTemplate
    {
        $definition = self::POPUP_EVENTS[$template->event_key] ?? null;
        if (!$definition) {
            throw new \InvalidArgumentException('Template event is not a predefined event.');
        }

        $template->update([
            'name' => $definition['label'],
            'category' => $definition['category'],
            'heading' => $definition['heading'],
            'subtitle' => $definition['subtitle'] ?? null,
            'body_text' => $definition['body_text'],
            'body_html' => '<p>' . nl2br(e($definition['body_text'])) . '</p>',
            'button_text' => $definition['button_text'] ?? null,
            'button_url' => $definition['button_url'] ?? null,
            'footer_text' => 'Mecarvi Embroidery',
            'status' => 'published',
            'variables' => $definition['variables'],
            'popup_size' => 'medium',
            'popup_position' => 'center',
            'overlay_opacity' => 60,
            'show_close_button' => true,
            'button_style' => 'primary',
            'auto_close_seconds' => null,
            'background_color' => null,
            'text_color' => null,
            'image_url' => null,
            'logo_url' => null,
            'logo_position' => 'center',
            'trigger_type' => 'event',
            'trigger_pages' => null,
            'display_frequency' => 'every_time',
        ]);

        return $template->fresh();
    }
}
