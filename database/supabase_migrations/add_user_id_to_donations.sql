-- Add user_id column to tdonations table
ALTER TABLE tdonations 
ADD COLUMN IF NOT EXISTS user_id INTEGER;

-- Add transaction_url column for storing Supabase Storage URLs
ALTER TABLE tdonations
ADD COLUMN IF NOT EXISTS transaction_url TEXT;

-- Drop existing constraint if it exists, then recreate
DO $$ 
BEGIN
    IF EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_tdonations_user_id') THEN
        ALTER TABLE tdonations DROP CONSTRAINT fk_tdonations_user_id;
    END IF;
END $$;

-- Add foreign key constraint to tusers table
ALTER TABLE tdonations
ADD CONSTRAINT fk_tdonations_user_id 
FOREIGN KEY (user_id) REFERENCES tusers(id) 
ON DELETE CASCADE;

-- Create index for better query performance
CREATE INDEX IF NOT EXISTS idx_tdonations_user_id ON tdonations(user_id);

-- Update existing donations to link them to users based on email (if needed)
-- UPDATE tdonations 
-- SET user_id = (SELECT id FROM tusers WHERE email = tdonations.donor_email LIMIT 1)
-- WHERE user_id IS NULL;
