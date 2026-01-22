# Azure Deployment Fix Guide

## Problem Summary
- ❌ Payment transaction proof upload fails on Azure
- ❌ Notifications not working properly on Azure
- ✅ Both work fine on localhost

## Root Causes
1. **Missing Environment Variable:** `SUPABASE_STORAGE_URL` not set in Azure
2. **Cached Files:** Old configuration/views cached on Azure
3. **Storage Bucket:** Payment bucket might not exist in Supabase

---

## Solution Steps

### Step 1: Add Missing Environment Variable to Azure

1. Go to **Azure Portal** → Your App Service
2. Navigate to **Settings** → **Environment variables**
3. Add this new variable:
   ```
   Name: SUPABASE_STORAGE_URL
   Value: https://lruvxbhfiogqolwztovs.supabase.co/storage/v1
   ```
4. **Save** the changes

### Step 2: Create Supabase Storage Bucket

1. Go to **Supabase Dashboard** → Your Project
2. Navigate to **Storage** section
3. Create a bucket named: `payments`
4. Set bucket to **Public** (so payment proof images can be viewed)
5. Configure CORS if needed:
   ```json
   {
     "allowedOrigins": ["*"],
     "allowedMethods": ["GET", "POST", "PUT", "DELETE"],
     "allowedHeaders": ["*"],
     "maxAge": 3600
   }
   ```

### Step 3: Clear Azure Cache via SSH/Kudu

**Option A: Using Kudu Console**
1. Go to Azure Portal → Your App Service
2. Navigate to **Advanced Tools** → **Go** (opens Kudu)
3. Click **Debug Console** → **CMD** or **PowerShell**
4. Navigate to your app directory (usually `/home/site/wwwroot`)
5. Run these commands:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

**Option B: Using Azure CLI**
```bash
az webapp ssh --name your-app-name --resource-group your-resource-group
cd /home/site/wwwroot
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

**Option C: Restart App Service**
1. Go to Azure Portal → Your App Service
2. Click **Restart** button
3. Wait for restart to complete

### Step 4: Commit and Push to Azure

1. **Add your actual Supabase URL to local `.env` file:**
   ```env
   SUPABASE_STORAGE_URL=https://lruvxbhfiogqolwztovs.supabase.co/storage/v1
   ```

2. **Commit the .env.example update:**
   ```bash
   git add .env.example
   git commit -m "Add SUPABASE_STORAGE_URL to environment configuration"
   git push
   ```

3. **Wait for Azure deployment** to complete (usually 1-2 minutes)

### Step 5: Verify the Fix

1. **Test Payment Upload:**
   - Login as parishioner on Azure deployment
   - Create a new request
   - Approve it as admin
   - Upload payment proof as parishioner
   - Should see success message ✅

2. **Test Notifications:**
   - Create a donation/request/payment
   - Check if admin sees notification with `user_id = null`
   - Check if parishioner sees notification with their `user_id`
   - Click notification bell → red dot should disappear ✅

---

## Verification Checklist

- [ ] `SUPABASE_STORAGE_URL` added to Azure environment variables
- [ ] `payments` bucket created in Supabase Storage
- [ ] Bucket is set to **Public**
- [ ] Cache cleared on Azure
- [ ] App Service restarted
- [ ] .env.example updated and pushed to Git
- [ ] Payment upload tested successfully
- [ ] Notifications showing correctly for admin
- [ ] Notifications showing correctly for parishioner
- [ ] Red notification dot disappearing when clicked

---

## Troubleshooting

### Payment Upload Still Fails
**Check Supabase Logs:**
1. Go to Supabase Dashboard → Logs → Storage
2. Look for upload errors
3. Verify bucket permissions

**Check Azure Application Logs:**
1. Azure Portal → App Service → Monitoring → Log stream
2. Look for error messages related to "Supabase upload failed"

### Notifications Still Not Working
**Check Database:**
```sql
-- Run in Supabase SQL Editor
SELECT id, type, message, user_id, read_at, created_at 
FROM tnotifications 
ORDER BY created_at DESC 
LIMIT 10;
```

Expected results:
- Admin notifications: `user_id = null`
- Parishioner notifications: `user_id = (specific parishioner ID)`

**Check AppServiceProvider:**
If notifications still don't work, verify your local changes are pushed:
```bash
git log --oneline -5
# Should see commit about AppServiceProvider notification fixes
```

---

## Environment Variables Summary

Make sure these are set in **Azure App Service → Environment variables**:

```
SUPABASE_URL=https://lruvxbhfiogqolwztovs.supabase.co
SUPABASE_SERVICE_ROLE_KEY=(your service role key)
SUPABASE_ANON_KEY=(your anon key)
SUPABASE_STORAGE_URL=https://lruvxbhfiogqolwztovs.supabase.co/storage/v1
SUPABASE_STORAGE_BUCKET_PAYMENTS=payments
```

---

## Quick Fix Command (Run on Azure via Kudu)

```bash
cd /home/site/wwwroot && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && echo "Cache cleared successfully!"
```

---

## Why This Happens

**Azure vs Localhost Differences:**

| Feature | Localhost | Azure |
|---------|-----------|-------|
| File Storage | Persistent local disk | Ephemeral (temporary) |
| Write Permissions | Full access | Restricted |
| Environment Variables | `.env` file | Azure Environment Variables |
| Cache | Cleared manually | Persists across deployments |

**Solution:** Use Supabase Storage (cloud storage) instead of local filesystem for all file uploads.

---

## Next Steps After Fix

Once everything is working:

1. **Test all upload features:**
   - Payment proof uploads
   - Donation proof uploads
   - Document uploads

2. **Monitor for 24 hours:**
   - Check Azure logs for any errors
   - Verify notifications continue working

3. **Update documentation:**
   - Add note about Supabase Storage requirement
   - Document environment variables for future deployments

---

## Need Help?

If issues persist after following these steps:

1. **Check Azure Application Logs:**
   - Azure Portal → App Service → Monitoring → Log stream

2. **Check Supabase Logs:**
   - Supabase Dashboard → Logs → Storage
   - Supabase Dashboard → Logs → Postgres

3. **Verify Environment Variables:**
   ```bash
   # In Azure Kudu console
   env | grep SUPABASE
   ```

4. **Test Supabase Connection:**
   ```bash
   # In Azure Kudu console
   php artisan tinker
   >>> env('SUPABASE_STORAGE_URL')
   # Should return the storage URL
   ```
