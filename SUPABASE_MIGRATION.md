# Supabase Migration Guide

## Overview
This guide will help you migrate your Laravel application from local MySQL (XAMPP) to Supabase (PostgreSQL).

## Prerequisites
1. Supabase account (https://supabase.com/)
2. PHP with PostgreSQL extensions (`php_pdo_pgsql`)

## Step 1: Create Supabase Project
1. Go to https://supabase.com/
2. Create a new project
3. Choose a region close to your users
4. Wait for the project to be provisioned

## Step 2: Get Database Connection Details
From your Supabase Dashboard, go to **Settings → Database**:

### Required Information:
- **Host**: `your-project-ref.supabase.co`
- **Database name**: `postgres`
- **Port**: `5432`
- **User**: `postgres`
- **Password**: [Set during project creation]

### Connection String Example:
```
postgresql://postgres:[YOUR-PASSWORD]@your-project-ref.supabase.co:5432/postgres
```

## Step 3: Get API Keys (Optional)
From **Settings → API**, copy:
- **Project URL**: `https://your-project-ref.supabase.co`
- **anon/public key**: For client-side operations
- **service_role key**: For server-side operations (keep secret!)

## Step 4: Update Laravel Configuration

### 4.1 Create/Update .env file:
```dotenv
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=your-project-ref.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password
DB_SSL_MODE=require

# Optional: Supabase API Configuration
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
```

### 4.2 Install PostgreSQL PHP Extension (if not installed):
```bash
# On Windows with XAMPP, download php_pdo_pgsql.dll
# Enable in php.ini: extension=pdo_pgsql

# On Ubuntu/Debian:
sudo apt-get install php-pgsql

# On macOS with Homebrew:
brew install php@8.1-pgsql
```

## Step 5: Run Schema Migration

### 5.1 Use Supabase SQL Editor:
1. Open your Supabase Dashboard
2. Go to **SQL Editor**
3. Copy and paste the contents of `database/supabase_schema.sql`
4. Run the query

### 5.2 Alternative - Use Laravel Migrations:
```bash
# Clear existing migration files and recreate them
php artisan migrate:fresh --seed
```

## Step 6: Test Connection
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Run a simple query
>>> DB::select('SELECT version()');
```

## Step 7: Update Laravel Models (if needed)

### Key Differences from MySQL:
1. **Boolean fields**: Use `true/false` instead of `1/0`
2. **ENUM replaced with CHECK constraints**
3. **SERIAL instead of AUTO_INCREMENT**
4. **Case sensitivity**: PostgreSQL is case-sensitive for identifiers

### Example Model Updates:
```php
// In your models, you might need to update:

// Before (MySQL):
protected $casts = [
    'is_paid' => 'string', // ENUM
];

// After (PostgreSQL):
protected $casts = [
    'is_paid' => 'string', // Still works with CHECK constraint
    'is_deleted' => 'boolean',
];
```

## Step 8: Row Level Security (RLS) - Optional

Supabase provides Row Level Security for better data protection:

```sql
-- Enable RLS on sensitive tables
ALTER TABLE "tusers" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "trequests" ENABLE ROW LEVEL SECURITY;

-- Create policies (example)
CREATE POLICY "Users can view own profile" ON "tusers"
  FOR SELECT USING (auth.uid()::text = id::text);
```

## Step 9: Deploy and Test

1. Update your production environment variables
2. Test all application features
3. Verify data integrity
4. Monitor performance

## Troubleshooting

### Common Issues:

1. **SSL Connection Error**:
   ```
   Add DB_SSL_MODE=require to .env
   ```

2. **Connection Refused**:
   ```
   Check firewall and verify connection details
   ```

3. **PHP Extension Missing**:
   ```
   Install php-pgsql extension
   ```

4. **Migration Errors**:
   ```
   Check PostgreSQL syntax differences from MySQL
   ```

## Performance Optimization

1. **Indexes**: Already included in schema
2. **Connection Pooling**: Supabase handles this
3. **Query Optimization**: Use `EXPLAIN ANALYZE` for slow queries

## Backup Strategy

1. **Automatic**: Supabase provides daily backups
2. **Manual**: Use `pg_dump` or Supabase CLI
3. **Laravel**: Create seeder files for important data

## Security Best Practices

1. **Environment Variables**: Never commit API keys
2. **Database Password**: Use strong passwords
3. **API Keys**: Rotate regularly
4. **Row Level Security**: Enable for sensitive data

## Support

- **Supabase Docs**: https://supabase.com/docs
- **Laravel PostgreSQL**: https://laravel.com/docs/database
- **Community**: Supabase Discord/GitHub