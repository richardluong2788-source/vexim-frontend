-- ============================================
-- VEXIM GLOBAL - DEFAULT DATA
-- Insert packages and admin user
-- ============================================

-- Insert Service Packages
INSERT INTO packages (name, price, duration_months, max_products, max_certificates, featured_listing, priority_support, analytics_access, description) VALUES
('Free', 0.00, 12, 5, 2, FALSE, FALSE, FALSE, 'Basic listing with limited features'),
('Silver', 299.00, 12, 20, 5, FALSE, FALSE, TRUE, 'Enhanced visibility with analytics'),
('Gold', 599.00, 12, 50, 10, TRUE, TRUE, TRUE, 'Featured listing with priority support'),
('Premium', 999.00, 12, NULL, NULL, TRUE, TRUE, TRUE, 'Unlimited products and certificates with maximum visibility');

-- Insert Default Admin User
-- Password: admin123 (hashed with bcrypt)
-- Note: In production, change this password immediately!
INSERT INTO users (email, password, role, full_name, phone, country, language, is_active, email_verified_at) VALUES
('admin@vexim.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '+84123456789', 'Vietnam', 'en', TRUE, NOW());

-- Note: Suppliers and Buyers will register through the frontend
