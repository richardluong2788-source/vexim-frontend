# Vexim Global v10 - Business Enhancement Deployment

## New Features Implemented

### 1. Buyer Contact Limits
- Buyers can only submit a limited number of contact requests per week based on their subscription plan
- Limits automatically reset every 7 days
- Clear error messages when limit is reached
- API endpoint to check remaining contacts: `GET /api/contacts/limit-status`

**Package Contact Limits:**
- Free: 1 contact per week
- Verified: 3 contacts per week  
- Premium: 10 contacts per week
- Enterprise: Unlimited contacts

### 2. Contact Admin Module (Support Tickets)
- Users can contact admin directly for support, complaints, or verification inquiries
- New `support_tickets` table tracks all support requests
- Admin dashboard to view and respond to tickets
- Queued email notifications for both users and admins
- Status tracking: open → in_progress → resolved → closed

**API Endpoints:**
- `POST /api/support/tickets` - Create ticket
- `GET /api/support/tickets` - Get user's tickets
- `GET /api/support/tickets/{id}` - Get ticket details
- `GET /api/admin/support/tickets` - Admin: Get all tickets
- `POST /api/admin/support/tickets/{id}/reply` - Admin: Reply to ticket
- `PUT /api/admin/support/tickets/{id}/status` - Admin: Update status

### 3. Supplier Visibility by Subscription
- Suppliers with higher-tier packages displayed more prominently
- Sorting hierarchy: visibility_level → rating → recent_activity
- New featured suppliers endpoint for homepage: `GET /api/suppliers/featured`

**Visibility Levels:**
- Level 1 (Free): Basic listing only, lower pages
- Level 2 (Verified): Display with verified badge, normal listing
- Level 3 (Premium): Featured + logo + top position, homepage + top list
- Level 4 (Enterprise): Featured + banner priority, homepage priority section

## Database Changes

### New Migrations
1. `2024_01_15_000001_add_contact_limits_and_visibility_to_packages.php`
   - Adds `contact_limit` to packages table
   - Adds `visibility_level` to packages and companies tables

2. `2024_01_15_000002_create_support_tickets_table.php`
   - Creates support_tickets table

3. `2024_01_15_000003_add_contact_tracking_to_users.php`
   - Adds `weekly_contact_count` and `contact_count_reset_at` to users table

### Data Seeding
Run the SQL script to populate existing packages with proper limits:
\`\`\`bash
php artisan db:seed --class=PackageLimitsSeeder
# Or manually run: scripts/seed_packages_with_limits.sql
\`\`\`

## Deployment Steps

1. **Backup Database**
   \`\`\`bash
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
   \`\`\`

2. **Pull Latest Code**
   \`\`\`bash
   cd ~/vexim-backend
   git pull origin main
   \`\`\`

3. **Run Migrations**
   \`\`\`bash
   php artisan migrate
   \`\`\`

4. **Seed Package Data**
   \`\`\`bash
   mysql -u username -p database_name < scripts/seed_packages_with_limits.sql
   \`\`\`

5. **Clear Caches**
   \`\`\`bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   \`\`\`

6. **Restart Queue Workers**
   \`\`\`bash
   php artisan queue:restart
   \`\`\`

7. **Test Key Features**
   - Test contact limit enforcement
   - Create a support ticket
   - Verify supplier sorting by visibility
   - Check featured suppliers endpoint

## Configuration

### Environment Variables
Add to `.env`:
\`\`\`env
# Admin email for support ticket notifications
MAIL_ADMIN_EMAIL=admin@veximglobal.com
\`\`\`

### Queue Configuration
Ensure queue workers are running for email notifications:
\`\`\`bash
# In cPanel, set up cron job:
* * * * * cd ~/vexim-backend && php artisan schedule:run >> /dev/null 2>&1
\`\`\`

## API Changes Summary

### New Endpoints
- `GET /api/contacts/limit-status` - Check buyer's contact limit status
- `GET /api/suppliers/featured` - Get featured suppliers for homepage
- `POST /api/support/tickets` - Create support ticket
- `GET /api/support/tickets` - Get user's tickets
- `GET /api/support/tickets/{id}` - Get ticket details
- `GET /api/admin/support/tickets` - Admin: Get all tickets
- `GET /api/admin/support/tickets/pending-count` - Admin: Get pending count
- `POST /api/admin/support/tickets/{id}/reply` - Admin: Reply to ticket
- `PUT /api/admin/support/tickets/{id}/status` - Admin: Update status

### Modified Endpoints
- `POST /api/contacts/` - Now checks contact limits and returns remaining count
- `GET /api/suppliers/search` - Now sorts by visibility_level first

## Testing Checklist

- [ ] Buyer with Free plan can only submit 1 contact per week
- [ ] Buyer with Premium plan can submit 10 contacts per week
- [ ] Contact limit resets after 7 days
- [ ] Clear error message when limit reached
- [ ] Support ticket creation works
- [ ] Admin receives email notification for new tickets
- [ ] User receives confirmation email
- [ ] Admin can reply to tickets
- [ ] User receives reply notification
- [ ] Supplier listing shows Enterprise/Premium suppliers first
- [ ] Featured suppliers endpoint returns high-tier suppliers
- [ ] Queue workers processing emails correctly

## Rollback Plan

If issues occur:
\`\`\`bash
# Rollback migrations
php artisan migrate:rollback --step=3

# Restore database backup
mysql -u username -p database_name < backup_YYYYMMDD.sql

# Revert code
git reset --hard HEAD~1
\`\`\`

## Support

For deployment assistance or issues:
- Email: dev@veximglobal.com
- Check logs: `~/vexim-backend/storage/logs/laravel.log`
- Queue logs: Check cron job logs in cPanel

---

**Deployment Date:** [To be filled]  
**Deployed By:** [To be filled]  
**Status:** Ready for Production
