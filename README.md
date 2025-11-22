# WooCommerce NBE Payment Gateway

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-green.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0%2B-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-777BB4.svg)

Custom payment gateway integration for NBE (National Bank of Egypt) Hosted Checkout with WooCommerce. This plugin enables Egyptian merchants to accept payments securely through NBE's payment gateway powered by Mastercard.

## 🔐 Important Security Notice

**CRITICAL:** This plugin requires API credentials from your NBE merchant account. **NEVER commit your actual credentials to version control.** All credentials must be entered through the WordPress admin interface only.

## ✨ Features

- ✅ Seamless NBE Hosted Checkout integration
- ✅ Test and Production mode support
- ✅ Secure payment processing via NBE Gateway
- ✅ Automatic order status updates
- ✅ Payment verification via NBE API
- ✅ Multi-currency support (via WooCommerce)
- ✅ Detailed error logging for debugging
- ✅ Admin configuration validation
- ✅ Translation ready (internationalization support)
- ✅ PCI-DSS compliant (hosted checkout)

## 📋 Requirements

Before installing this plugin, ensure your environment meets these requirements:

- **WordPress:** 5.0 or higher
- **WooCommerce:** 3.0 or higher
- **PHP:** 7.0 or higher
- **SSL Certificate:** HTTPS required for production
- **NBE Merchant Account:** With API access enabled
- **Server Requirements:** 
  - PHP cURL extension
  - PHP JSON extension
  - WordPress HTTP API enabled

## 🚀 Installation

### Method 1: WordPress Admin Panel (Recommended)

1. Download the latest release ZIP file from the repository
2. Log in to your WordPress admin panel
3. Navigate to **Plugins** > **Add New** > **Upload Plugin**
4. Click **Choose File** and select the downloaded ZIP
5. Click **Install Now**
6. After installation, click **Activate Plugin**

### Method 2: Manual Installation via FTP

1. Download and extract the plugin ZIP file
2. Connect to your server via FTP/SFTP
3. Upload the `woocommerce-nbe-payment` folder to `/wp-content/plugins/`
4. Go to WordPress admin > **Plugins**
5. Find "WooCommerce NBE Payment Gateway" and click **Activate**

### Method 3: Git Clone (For Developers)

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration.git woocommerce-nbe-payment
```

Then activate the plugin from WordPress admin panel.

## ⚙️ Configuration

### Step 1: Obtain NBE Credentials

Before configuring the plugin, you need to obtain the following from your NBE merchant account:

1. **Merchant ID** - Your unique NBE merchant identifier (e.g., `YOUR_MERCHANT_ID`)
2. **API Username** - Format is usually `merchant.YOUR_MERCHANT_ID`
3. **API Password** - Your secure API password provided by NBE

> **Note:** Contact NBE support if you don't have these credentials or need to activate API access on your merchant account.

### Step 2: Plugin Configuration

1. Log in to your WordPress admin panel
2. Navigate to **WooCommerce** > **Settings** > **Payments**
3. Find **NBE Hosted Checkout** in the payment methods list
4. Click **Manage** or toggle the switch to enable it
5. Configure the following settings:

#### Basic Settings

| Setting | Description | Required | Example |
|---------|-------------|----------|---------|
| **Enable/Disable** | Enable this payment method for customers | Yes | ✅ Checked |
| **Title** | Payment method name shown at checkout | Yes | "NBE Payment" or "Pay with Card" |
| **Description** | Brief description shown to customers | No | "Pay securely with your credit/debit card" |

#### API Credentials

| Setting | Description | Required | Example |
|---------|-------------|----------|---------|
| **Merchant ID** | Your NBE Merchant ID | Yes | `YOUR_MERCHANT_ID` |
| **API Username** | Your NBE API Username | Yes | `merchant.YOUR_MERCHANT_ID` |
| **API Password** | Your NBE API Password | Yes | `your_secure_password` |

#### Environment Settings

| Setting | Description | Required | Default |
|---------|-------------|----------|---------|
| **Test Mode** | Use NBE test environment | Yes | ✅ Enabled (for testing) |

6. Click **Save changes**

### Step 3: Test the Integration

1. Keep **Test Mode** enabled initially
2. Ensure you're using test credentials (if different from production)
3. Create a test product in WooCommerce
4. Place a test order on your site
5. Complete the payment using NBE's test card numbers
6. Verify that:
   - Payment page loads correctly
   - Order status updates to "Processing" after payment
   - Payment confirmation email is sent
   - Order appears in WooCommerce > Orders

### Step 4: Go Live

Once testing is complete and everything works correctly:

1. **Disable Test Mode** in the plugin settings
2. **Enter production credentials** (Merchant ID, Username, Password)
3. **Verify SSL is active** - Your site must use HTTPS
4. **Test with a small real transaction** to confirm everything works
5. **Monitor the first few live orders** carefully

## 🎨 Customization

### Changing Payment Method Title

The title shown to customers at checkout can be customized:

```
WooCommerce > Settings > Payments > NBE Payment > Manage > Title
```

Examples:
- "Credit/Debit Card"
- "Pay with NBE"
- "Secure Card Payment"
- "بطاقة الائتمان" (Arabic)

### Modifying Customer Description

Customize the description shown during checkout:

```
WooCommerce > Settings > Payments > NBE Payment > Manage > Description
```

Examples:
- "Pay securely with your Visa, Mastercard, or local bank card"
- "Safe and secure payment powered by NBE"
- "دفع آمن عبر البنك الأهلي المصري" (Arabic)

## 🧪 Testing

### Test Mode Configuration

1. Enable **Test Mode** in plugin settings
2. Use NBE's test environment credentials
3. Test transactions will not process real money
4. Use NBE's test card numbers for testing

### Test Card Numbers

Contact NBE support for test card numbers specific to your merchant account. Common test scenarios include:

- Successful payment
- Declined payment
- Insufficient funds
- Invalid card number
- Expired card

### Debug Logging

Enable WordPress debug logging to troubleshoot issues:

1. Edit `wp-config.php` file
2. Add the following lines:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

3. Check logs at: `wp-content/debug.log`
4. Look for entries starting with "NBE Payment:"

## 🔧 Troubleshooting

### Payment Gateway Not Showing at Checkout

**Symptoms:** NBE payment option doesn't appear for customers

**Solutions:**
- Verify the plugin is activated: **Plugins** > **Installed Plugins**
- Check "Enable NBE Hosted Checkout" is checked in settings
- Ensure all credentials (Merchant ID, Username, Password) are entered
- Verify WooCommerce is active and properly configured
- Check if your country/currency is supported

### Payment Fails Immediately

**Symptoms:** Payment fails without showing the payment page

**Solutions:**
- Verify API credentials are correct (no extra spaces)
- Confirm you're using the right environment (test vs production)
- Check Merchant ID format matches your NBE account
- Ensure your site has a valid SSL certificate (HTTPS)
- Review WordPress error logs for details
- Contact NBE support to verify your API access is active

### "Payment Gateway Not Configured" Error

**Symptoms:** Error message about missing configuration

**Solutions:**
- Go to **WooCommerce** > **Settings** > **Payments** > **NBE Payment**
- Verify all three credentials are entered:
  - Merchant ID
  - API Username
  - API Password
- Check for any extra spaces before or after credentials
- Save settings again

### Order Stuck in "Pending Payment"

**Symptoms:** Order remains pending after payment attempt

**Solutions:**
- Check if customer completed the payment on NBE's page
- Verify callback URL is accessible (not blocked by firewall)
- Review error logs for callback failures
- Manually verify payment status in NBE merchant portal
- Update order status manually if payment was successful

### SSL Certificate Errors

**Symptoms:** SSL-related errors in logs

**Solutions:**
- Ensure your site has a valid SSL certificate
- Verify HTTPS is working properly
- Check SSL certificate is not expired
- Contact your hosting provider if SSL issues persist
- Test SSL using online tools like SSL Labs

### Session Creation Failed

**Symptoms:** Error creating payment session with NBE

**Solutions:**
- Verify API credentials are correct
- Check your IP is not blocked by NBE
- Ensure your merchant account is active
- Verify API access is enabled on your account
- Contact NBE support for API troubleshooting

## 📊 Payment Flow

Understanding how the payment process works:

```
1. Customer clicks "Place Order" at checkout
   ↓
2. Plugin creates checkout session with NBE API
   ↓
3. Customer is redirected to NBE payment page
   ↓
4. Customer enters card details on secure NBE page
   ↓
5. NBE processes the payment
   ↓
6. Customer redirected back to your site
   ↓
7. Plugin verifies payment with NBE API
   ↓
8. Order status updated (Success/Failed)
   ↓
9. Customer sees order confirmation or error message
```

## 🔒 Security Best Practices

### For Site Administrators

#### Credential Management
- ✅ Store credentials only in WordPress admin settings
- ✅ Never share API credentials with anyone
- ✅ Use strong, unique passwords for API access
- ✅ Rotate credentials periodically (every 6-12 months)
- ✅ Revoke credentials immediately if compromised

#### WordPress Security
- ✅ Keep WordPress core updated to latest version
- ✅ Keep WooCommerce updated to latest version
- ✅ Keep this plugin updated to latest version
- ✅ Use strong admin passwords (12+ characters)
- ✅ Enable two-factor authentication for admin accounts
- ✅ Limit admin user access to trusted individuals only
- ✅ Install security plugins (Wordfence, Sucuri, etc.)
- ✅ Perform regular security audits
- ✅ Monitor login attempts and access logs

#### SSL/TLS Requirements
- ✅ **HTTPS is mandatory** for production environments
- ✅ Use valid SSL certificates (not self-signed)
- ✅ Keep SSL certificates updated before expiration
- ✅ Use TLS 1.2 or higher
- ✅ Configure strong cipher suites
- ✅ Enable HSTS (HTTP Strict Transport Security)

#### Server Security
- ✅ Choose reputable hosting providers
- ✅ Enable server firewall
- ✅ Keep server software updated (PHP, MySQL, etc.)
- ✅ Use SFTP instead of FTP
- ✅ Implement regular backup schedule
- ✅ Set proper file permissions (644 for files, 755 for directories)
- ✅ Disable directory listing

#### Monitoring
- ✅ Monitor transactions regularly
- ✅ Review order logs weekly
- ✅ Check for unusual activity patterns
- ✅ Set up email alerts for failed payments
- ✅ Review error logs periodically

### PCI Compliance

This plugin uses **NBE Hosted Checkout**, which means:

- ✅ Card data is entered directly on NBE's secure payment page
- ✅ Card information **never touches your server**
- ✅ This **minimizes your PCI compliance scope**
- ✅ NBE handles all card data security requirements

**Your Responsibilities:**
- Use HTTPS for your entire website
- Keep WordPress and plugins updated
- Implement basic security measures listed above
- Monitor for security breaches
- Have an incident response plan

**What This Plugin Does NOT Do:**
- ❌ Store credit card numbers
- ❌ Process card data directly
- ❌ Handle sensitive cardholder data
- ❌ Store CVV/CVC codes

## 🌍 Internationalization

This plugin is translation-ready and can be translated to any language.

### Available Languages

Currently available translations:
- English (default)

### Adding Your Language

To translate the plugin to your language:

1. Install and activate a translation plugin like **Loco Translate**
2. Go to **Loco Translate** > **Plugins** > **WooCommerce NBE Payment Gateway**
3. Click **New language**
4. Select your language and click **Start translating**
5. Translate all strings and save

Alternatively, use translation files (`.po` and `.mo`) in the `languages/` folder.

## 📞 Support

### Plugin Issues

For issues related to the plugin functionality:

1. **Check Documentation:** Review this README and troubleshooting section
2. **Enable Debug Logging:** Check error logs for specific issues
3. **GitHub Issues:** [Open an issue on GitHub](https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration/issues)
   - Provide WordPress version
   - Provide WooCommerce version
   - Provide PHP version
   - Include relevant error logs (remove sensitive data)
   - Describe steps to reproduce the issue

### NBE API Issues

For issues related to NBE's payment gateway:

- **NBE Support:** Contact NBE support directly
- **Merchant Portal:** Check your NBE merchant dashboard
- **API Credentials:** Verify your credentials are active
- **Transaction Issues:** Review transaction details in NBE portal

### Before Contacting Support

Please gather this information:

- WordPress version (Dashboard > Updates)
- WooCommerce version (Plugins > Installed Plugins)
- PHP version (Tools > Site Health > Info)
- Plugin version
- Error messages (from logs)
- Steps to reproduce the issue
- Screenshots (if applicable)

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

### Reporting Bugs

1. Check if the bug is already reported in [GitHub Issues](https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration/issues)
2. If not, create a new issue with:
   - Clear description of the bug
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots if applicable
   - Your environment details

### Suggesting Features

1. Open a new issue with the "Feature Request" label
2. Describe the feature and its benefits
3. Provide use cases and examples
4. Be open to discussion and feedback

### Submitting Code

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes following WordPress coding standards
4. Test thoroughly
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

### Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Write clear, commented code
- Test all changes thoroughly
- Update documentation as needed
- Never commit sensitive data (credentials, customer info, etc.)

## 📈 Changelog

### Version 1.0.1 (Current)
- **Security:** Removed all hardcoded credentials for secure GitHub hosting
- **Security:** Added credential validation and error handling
- **Enhancement:** Added admin notices for missing credentials
- **Enhancement:** Improved error logging with prefixed messages
- **Enhancement:** Added internationalization support
- **Enhancement:** Added translation-ready strings
- **Enhancement:** Added `is_configured()` validation method
- **Enhancement:** Better error messages for users and admins
- **Fix:** Added check for WooCommerce class existence
- **Fix:** Added file existence check before enqueueing scripts
- **Fix:** Improved input sanitization
- **Documentation:** Enhanced README with comprehensive setup guide
- **Documentation:** Added SECURITY.md with security best practices

### Version 1.0.0
- Initial release
- NBE Hosted Checkout integration
- Test and Production mode support
- Payment session creation
- Order status management
- Payment verification
- Basic error handling

## ⚠️ Disclaimer

This plugin is provided "as is" without warranty of any kind, express or implied. The authors and contributors are not responsible for any issues, damages, or losses arising from the use of this plugin.

### Important Notes:

- **Testing Required:** Always test thoroughly in a staging environment before using in production
- **Compliance:** Ensure compliance with all applicable laws and regulations in your jurisdiction
- **PCI DSS:** While this plugin uses hosted checkout to minimize PCI scope, you are responsible for your overall PCI compliance
- **Data Privacy:** Comply with data protection regulations (GDPR, etc.) in your region
- **Support:** Plugin support is provided on a best-effort basis through GitHub Issues
- **NBE Changes:** The plugin may require updates if NBE changes their API
- **Backups:** Always maintain regular backups of your site and database

## 🙏 Credits

- **Author:** Ahmed Khalaf
- **Payment Gateway:** National Bank of Egypt (NBE)
- **Payment Platform:** Mastercard Gateway
- **Built For:** WordPress / WooCommerce

## 📌 Links

- **GitHub Repository:** [WooCommerce NBE Payment Integration](https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration/)
- **Author Profile:** [Ahmed Khalaf on GitHub](https://github.com/AhmedAlaaKhalaf/)
- **Report Issues:** [GitHub Issues](https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration/issues)
- **NBE Website:** [National Bank of Egypt](https://www.nbe.com.eg/)

---

**Made with ❤️ for the Egyptian WordPress community**

*Last Updated: January 2025*
