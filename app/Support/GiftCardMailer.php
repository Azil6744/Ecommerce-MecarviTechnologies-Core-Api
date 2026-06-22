<?php

namespace App\Support;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GiftCardMailer
{
    public static function sendIssued(string $email, array $data): bool
    {
        return self::sendEmail(
            $email,
            'gift-card-issued',
            'Your Gift Card is Ready!',
            $data
        );
    }

    public static function sendTransferredToOldOwner(string $email, array $data): bool
    {
        return self::sendEmail(
            $email,
            'gift-card-transferred-sender',
            'Gift Card Transferred Successfully',
            $data
        );
    }

    public static function sendTransferredToNewOwner(string $email, array $data): bool
    {
        return self::sendEmail(
            $email,
            'gift-card-transferred-recipient',
            'You Received a Gift Card!',
            $data
        );
    }

    public static function sendExpired(string $email, array $data): bool
    {
        return self::sendEmail(
            $email,
            'gift-card-expired',
            'Your Gift Card Has Expired',
            $data
        );
    }

    protected static function sendEmail(string $email, string $slug, string $defaultSubject, array $data): bool
    {
        try {
            $template = EmailTemplate::where('slug', $slug)->first();
            $subject = $defaultSubject;
            $body = '';

            if ($template) {
                $subject = self::replaceVariables($template->subject ?: $defaultSubject, $data);
                $body = self::replaceVariables($template->body_html, $data);
            } else {
                $body = self::getDefaultHtml($slug, $data);
            }

            Mail::html($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send gift card email ($slug) to $email: " . $e->getMessage());
            return false;
        }
    }

    protected static function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', (string) $value, $content);
        }
        return $content;
    }

    protected static function getDefaultHtml(string $slug, array $data): string
    {
        $code = $data['code'] ?? '';
        $balance = isset($data['balance']) ? number_format($data['balance'], 2) : '0.00';
        $message = $data['message'] ?? '';
        $recipientName = $data['recipient_name'] ?? 'Valued Customer';
        $senderName = $data['sender_name'] ?? '';
        $expiresAt = $data['expires_at'] ?? '';
        $oldOwnerEmail = $data['old_owner_email'] ?? '';
        $newOwnerEmail = $data['new_owner_email'] ?? '';

        $style = "
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
            .container { max-width: 600px; margin: 40px auto; padding: 32px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
            .header { text-align: center; margin-bottom: 32px; }
            .logo { font-size: 24px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.1em; }
            .card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border-radius: 16px; padding: 32px; text-align: center; margin: 24px 0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden; }
            .card-title { font-size: 14px; text-transform: uppercase; letter-spacing: 0.2em; color: #94a3b8; margin-bottom: 24px; }
            .card-code { font-family: 'Courier New', Courier, monospace; font-size: 28px; font-weight: 700; letter-spacing: 4px; color: #38bdf8; background: rgba(255, 255, 255, 0.1); padding: 12px; border-radius: 8px; display: inline-block; margin-bottom: 24px; }
            .card-balance { font-size: 36px; font-weight: 800; color: #f8fafc; }
            .card-balance span { font-size: 18px; font-weight: 500; color: #cbd5e1; }
            .info-section { margin-top: 32px; line-height: 1.6; }
            .personal-msg { background-color: #f1f5f9; border-left: 4px solid #64748b; padding: 16px; border-radius: 0 8px 8px 0; font-style: italic; margin: 20px 0; }
            .footer { text-align: center; margin-top: 32px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 24px; }
        ";

        switch ($slug) {
            case 'gift-card-issued':
                return "
                <html>
                <head><style>{$style}</style></head>
                <body>
                    <div class='container'>
                        <div class='header'><div class='logo'>Mecarvi Embroidery</div></div>
                        <h2>Hello {$recipientName},</h2>
                        <p>We are excited to let you know that you have been issued a gift card!</p>
                        " . ($senderName ? "<p><strong>From:</strong> {$senderName}</p>" : "") . "
                        
                        <div class='card'>
                            <div class='card-title'>Gift Card Code</div>
                            <div class='card-code'>{$code}</div>
                            <div class='card-balance'><span>$</span>{$balance}</div>
                        </div>

                        " . ($message ? "<div class='personal-msg'>\"{$message}\"</div>" : "") . "

                        <div class='info-section'>
                            <p>To use this gift card, simply enter the 15-digit code during checkout on our store.</p>
                            " . ($expiresAt ? "<p><strong>Expires On:</strong> {$expiresAt}</p>" : "") . "
                        </div>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Mecarvi Embroidery. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";

            case 'gift-card-transferred-sender':
                return "
                <html>
                <head><style>{$style}</style></head>
                <body>
                    <div class='container'>
                        <div class='header'><div class='logo'>Mecarvi Embroidery</div></div>
                        <h2>Hello,</h2>
                        <p>You have successfully transferred your gift card to <strong>{$newOwnerEmail}</strong>.</p>
                        
                        <div class='card'>
                            <div class='card-title'>Transferred Gift Card</div>
                            <div class='card-balance'><span>$</span>{$balance}</div>
                            <div class='card-code' style='font-size: 20px; color: #94a3b8;'>CODE REDACTED FOR SECURITY</div>
                        </div>

                        <p>The new owner can now access and redeem this gift card using their account or the code sent to them.</p>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Mecarvi Embroidery. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";

            case 'gift-card-transferred-recipient':
                return "
                <html>
                <head><style>{$style}</style></head>
                <body>
                    <div class='container'>
                        <div class='header'><div class='logo'>Mecarvi Embroidery</div></div>
                        <h2>Hello,</h2>
                        <p>A gift card has been transferred to you from <strong>{$oldOwnerEmail}</strong>!</p>
                        
                        <div class='card'>
                            <div class='card-title'>Your Gift Card Code</div>
                            <div class='card-code'>{$code}</div>
                            <div class='card-balance'><span>$</span>{$balance}</div>
                        </div>

                        <div class='info-section'>
                            <p>This gift card is now linked to your account. You can use it during checkout by entering the code above.</p>
                            " . ($expiresAt ? "<p><strong>Expires On:</strong> {$expiresAt}</p>" : "") . "
                        </div>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Mecarvi Embroidery. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";

            case 'gift-card-expired':
                return "
                <html>
                <head><style>{$style}</style></head>
                <body>
                    <div class='container'>
                        <div class='header'><div class='logo'>Mecarvi Embroidery</div></div>
                        <h2>Hello {$recipientName},</h2>
                        <p>We are writing to inform you that your gift card has expired.</p>
                        
                        <div class='card' style='background: linear-gradient(135deg, #475569 0%, #334155 100%);'>
                            <div class='card-title' style='color: #cbd5e1;'>Expired Gift Card</div>
                            <div class='card-code' style='text-decoration: line-through; color: #94a3b8;'>{$code}</div>
                            <div class='card-balance' style='color: #cbd5e1;'><span>$</span>{$balance}</div>
                        </div>

                        <p>As the expiration date has passed, any remaining balance has been locked and the card is no longer active.</p>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Mecarvi Embroidery. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";

            default:
                return "
                <html>
                <head><style>{$style}</style></head>
                <body>
                    <div class='container'>
                        <div class='header'><div class='logo'>Mecarvi Embroidery</div></div>
                        <h2>Gift Card Notification</h2>
                        <p>Code: {$code}</p>
                        <p>Balance: \${$balance}</p>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Mecarvi Embroidery. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";
        }
    }
}
