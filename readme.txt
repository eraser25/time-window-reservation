=== Time Window Reservation & Fair Distribution ===
Contributors: yourname
Tags: woocommerce, reservation, fair-distribution, e-commerce
Requires at least: 5.9
Requires PHP: 7.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A production-ready WooCommerce plugin that replaces "first come, first serve" with an intelligent time-window-based reservation system and fair distribution algorithm.

== Description ==

This plugin provides:

* **Time Window Reservations** - Set specific periods when customers can join a reservation
* **Fair Distribution Algorithm** - Intelligently distributes products based on:
  - Account membership duration
  - Recent win history (rewards users who haven't won recently)
  - Join time (users who joined early get slight bonus)
  - Small random factor for additional fairness
  
* **Payment Windows** - Winners have a limited time to complete payment
* **Automatic Backup Assignment** - When winners don't pay, backups automatically become winners
* **Points System** - Non-winners earn redeemable points
* **Security Features**:
  - Device fingerprinting
  - IP address tracking
  - Rate limiting
  - reCAPTCHA integration
  - Comprehensive audit logs
  
* **Admin Dashboard** - Full control with:
  - Live participant tracking
  - Winner/backup/non-winner lists
  - Manual override capabilities
  - Detailed audit logging

== Installation ==

1. Upload `time-window-reservation` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress
3. Go to Time Window Reservation > Settings to configure
4. For each product, edit the product and configure reservation settings

== Usage ==

1. Edit any WooCommerce product
2. Scroll to "Time Window Reservation Settings"
3. Configure:
   - Reservation start/end times
   - Stock available
   - Backup count
   - Points reward for non-winners
   - Algorithm weights
   - Cooldown period

4. Use shortcodes on the product page:
   - `[twrf_countdown product_id="123"]` - Show countdown timer
   - `[twrf_participant_count product_id="123"]` - Show participant count
   - `[twrf_reservation_button product_id="123"]` - Show join button
   - `[twrf_user_points]` - Show user's current points

== Algorithm Explanation ==

Each participant receives a score calculated as:

score = (membership_weight × user_age_factor) 
      + (no_recent_win_bonus × user_no_win_factor)
      + (join_time_weight × early_join_factor)
      + (random_factor × random(0-1))

Where:
- user_age_factor: Min(years_registered / 1, 1.0)
- user_no_win_factor: Based on days since last win (max 30 days = 0.5 bonus)
- early_join_factor: Earlier joiner gets higher score
- random_factor: Small randomness (default 5%) ensures unpredictability

Users are ranked by score descending. Top N users (where N = stock) become winners, next M become backups, rest get points.

== Security ==

This plugin includes:
- Database-level locking to prevent race conditions
- Nonce verification on all AJAX endpoints
- Comprehensive input sanitization
- IP tracking and device fingerprinting for abuse detection
- Rate limiting (configurable attempts per window)
- Full audit logging of all actions
- reCAPTCHA v3 integration support

== Database Tables ==

Creates 6 custom tables:
- twrf_reservations - Reservation configurations
- twrf_sessions - Participation records
- twrf_win_sessions - Winner/backup/payment tracking
- twrf_user_points - User point balances
- twrf_point_logs - Point transaction history
- twrf_audit_logs - Complete action audit trail

== Performance ==

Optimized for:
- 10,000+ concurrent participants
- Indexed database queries
- Efficient scoring algorithm (O(n log n))
- AJAX-based UI updates
- Server-time validation (prevents client manipulation)

== Frequently Asked Questions ==

= Is this a lottery system? =
No. This is a deterministic, fair-distribution system. Winners are selected based on transparent criteria: account age, win history, join timing, and minimal randomness.

= Can I customize the algorithm weights? =
Yes. Go to the product edit page and adjust the algorithm weights to suit your needs.

= What happens if a winner doesn't pay? =
Their payment window expires, and the next backup automatically becomes a winner with a new payment deadline.

= Can customers see why they weren't selected? =
Not in the frontend, but all data is logged. You can implement transparency via email/dashboard if desired.

== Changelog ==

= 1.0.0 =
* Initial release
* Fair distribution algorithm
* Payment windows and backup assignment
* Points system
* Admin dashboard
* Security features

== Support ==

For issues and feature requests, please visit the plugin repository or contact support.

== License ==

This plugin is licensed under GPL v2 or later.