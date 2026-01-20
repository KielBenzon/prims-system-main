-- Add deleted_at column to tdocuments table for soft delete support
-- Run this in Supabase SQL Editor

ALTER TABLE tdocuments 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP WITH TIME ZONE DEFAULT NULL;

-- Create index on deleted_at for better query performance
CREATE INDEX IF NOT EXISTS idx_tdocuments_deleted_at ON tdocuments(deleted_at);

-- Verify column was added
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'tdocuments' 
ORDER BY ordinal_position;
