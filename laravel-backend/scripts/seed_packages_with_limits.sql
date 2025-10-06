-- Update existing packages with contact limits and visibility levels
-- Run this after running the migrations

-- Free Package
UPDATE packages 
SET contact_limit = 1, visibility_level = 1 
WHERE name = 'Free' OR price = 0;

-- Verified Package
UPDATE packages 
SET contact_limit = 3, visibility_level = 2 
WHERE name LIKE '%Verified%' OR name LIKE '%Basic%';

-- Premium Package
UPDATE packages 
SET contact_limit = 10, visibility_level = 3 
WHERE name LIKE '%Premium%' OR name LIKE '%Standard%';

-- Enterprise Package (unlimited contacts)
UPDATE packages 
SET contact_limit = 0, visibility_level = 4 
WHERE name LIKE '%Enterprise%' OR name LIKE '%Unlimited%';

-- Update companies visibility_level based on their current package
UPDATE companies c
INNER JOIN packages p ON c.package_id = p.id
SET c.visibility_level = p.visibility_level
WHERE c.package_id IS NOT NULL;

-- Set default visibility for companies without packages
UPDATE companies 
SET visibility_level = 1 
WHERE package_id IS NULL OR visibility_level IS NULL;
