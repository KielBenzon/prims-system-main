-- Add request_id column to tcertificate_details table
-- Run this SQL in your Supabase SQL Editor

ALTER TABLE tcertificate_details 
ADD COLUMN request_id BIGINT;

-- Add foreign key constraint
ALTER TABLE tcertificate_details
ADD CONSTRAINT fk_certificate_details_request
FOREIGN KEY (request_id) 
REFERENCES trequests(id) 
ON DELETE CASCADE;

-- Add index for better query performance
CREATE INDEX idx_certificate_details_request_id 
ON tcertificate_details(request_id);
