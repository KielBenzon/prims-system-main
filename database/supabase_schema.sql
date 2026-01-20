-- PostgreSQL/Supabase Compatible Schema
-- Converted from MySQL schema.sql

-- Create tables with PostgreSQL syntax
CREATE TABLE IF NOT EXISTS "tusers" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(255) NOT NULL,
    "email" VARCHAR(255) NOT NULL UNIQUE,
    "password" VARCHAR(255) NOT NULL,
    "role" VARCHAR(20) NOT NULL DEFAULT 'Admin' CHECK (role IN ('Admin', 'Parishioner')),
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tpriests" (
    "id" SERIAL PRIMARY KEY,
    "last_name" VARCHAR(255) NOT NULL,
    "first_name" VARCHAR(255) NOT NULL,
    "middle_name" VARCHAR(255) NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "date_of_birth" DATE NOT NULL,
    "phone_number" VARCHAR(255) NOT NULL,
    "email_address" VARCHAR(255) NOT NULL,
    "ordination_date" DATE NOT NULL,
    "image" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tdonations" (
    "id" SERIAL PRIMARY KEY,
    "donor_name" VARCHAR(255) NOT NULL,
    "donor_email" VARCHAR(255) NOT NULL,
    "donor_phone" VARCHAR(255) NOT NULL,
    "amount" DECIMAL(10, 2) NOT NULL,
    "donation_date" DATE NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tmail" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL,
    "sender" VARCHAR(255) NOT NULL,
    "recipient" VARCHAR(255) NOT NULL,
    "subject" VARCHAR(255) NOT NULL,
    "priority" VARCHAR(20) NOT NULL DEFAULT 'Normal' CHECK (priority IN ('Very High', 'High', 'Normal', 'Low')),
    "status" VARCHAR(20) NOT NULL DEFAULT 'Undelivered' CHECK (status IN ('Undelivered', 'Delivered')),
    "date" DATE NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tdocuments" (
    "id" SERIAL PRIMARY KEY,
    "document_type" VARCHAR(255) NOT NULL,
    "full_name" VARCHAR(255) NOT NULL,
    "file" VARCHAR(255) NOT NULL,
    "uploaded_by" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tcertificate_types" (
    "id" SERIAL PRIMARY KEY,
    "certificate_type" VARCHAR(255) NOT NULL UNIQUE,
    "description" VARCHAR(255) NOT NULL,
    "amount" DECIMAL(10, 2) NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert default certificate types
INSERT INTO "tcertificate_types" ("certificate_type", "description", "amount") VALUES
('Baptismal Certificate', 'Baptismal Certificate', 100.00),
('Marriage Certificate', 'Marriage Certificate', 100.00),
('Death Certificate', 'Death Certificate', 100.00),
('Confirmation Certificate', 'Confirmation Certificate', 100.00)
ON CONFLICT (certificate_type) DO NOTHING;

CREATE TABLE IF NOT EXISTS "tcertificate_details" (
    "id" SERIAL PRIMARY KEY,
    "certificate_type" VARCHAR(255) NOT NULL,
    
    -- Baptismal Certificate
    "name_of_child" VARCHAR(255),
    "date_of_birth" DATE,
    "place_of_birth" VARCHAR(255),
    "baptism_schedule" DATE,
    "name_of_father" VARCHAR(255),
    "name_of_mother" VARCHAR(255),

    -- Marriage Certificate - Bride Information
    "bride_name" VARCHAR(255),
    "birthdate_bride" DATE,
    "age_bride" INTEGER,
    "birthplace_bride" VARCHAR(255),
    "citizenship_bride" VARCHAR(255),
    "religion_bride" VARCHAR(255),
    "residence_bride" VARCHAR(255),
    "civil_status_bride" VARCHAR(255),
    "name_of_father_bride" VARCHAR(255),
    "name_of_mother_bride" VARCHAR(255),

    -- Marriage Certificate - Groom Information
    "name_of_groom" VARCHAR(255),
    "birthdate_groom" DATE,
    "age_groom" INTEGER,
    "birthplace_groom" VARCHAR(255),
    "citizenship_groom" VARCHAR(255),
    "religion_groom" VARCHAR(255),
    "residence_groom" VARCHAR(255),
    "civil_status_groom" VARCHAR(255),
    "name_of_father_groom" VARCHAR(255),
    "name_of_mother_groom" VARCHAR(255),

    -- Death Certificate
    "first_name_death" VARCHAR(255),
    "middle_name_death" VARCHAR(255),
    "last_name_death" VARCHAR(255),
    "date_of_birth_death" DATE,
    "date_of_death" DATE,
    "file_death" VARCHAR(255),

    -- Confirmation Certificate
    "name_of_confirmand" VARCHAR(255),
    "date_of_birth_confirmand" DATE,
    "date_of_confirmation" DATE,
    "file_confirmation" VARCHAR(255),

    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_certificate_type FOREIGN KEY ("certificate_type") REFERENCES "tcertificate_types"("certificate_type")
);

CREATE TABLE IF NOT EXISTS "trequests" (
    "id" SERIAL PRIMARY KEY,
    "requested_by" INTEGER NOT NULL,
    "document_type" VARCHAR(255) NOT NULL,
    "approved_by" INTEGER DEFAULT NULL,
    "status" VARCHAR(50) NOT NULL,
    "is_paid" VARCHAR(10) NOT NULL DEFAULT 'Unpaid' CHECK (is_paid IN ('Paid', 'Unpaid')),
    "is_deleted" BOOLEAN NOT NULL DEFAULT FALSE,
    "notes" TEXT DEFAULT NULL,
    "file" VARCHAR(255) DEFAULT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tpayments" (
    "id" SERIAL PRIMARY KEY,
    "request_id" INTEGER NOT NULL,
    "amount" DECIMAL(10, 2) NOT NULL,
    "payment_date" DATE NOT NULL,
    "payment_method" VARCHAR(255) NOT NULL,
    "payment_status" VARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (payment_status IN ('Pending', 'Paid')),
    "transaction_id" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_request_id FOREIGN KEY ("request_id") REFERENCES "trequests"("id")
);

CREATE TABLE IF NOT EXISTS "tannouncements" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL,
    "content" TEXT NOT NULL,
    "assigned_priest" INTEGER NOT NULL,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_assigned_priest FOREIGN KEY ("assigned_priest") REFERENCES "tpriests"("id")
);

CREATE TABLE IF NOT EXISTS "tnotifications" (
    "id" SERIAL PRIMARY KEY,
    "type" INTEGER NOT NULL,
    "message" TEXT NOT NULL,
    "is_read" BOOLEAN NOT NULL DEFAULT FALSE,
    "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_announcement_type FOREIGN KEY ("type") REFERENCES "tannouncements"("id")
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_tusers_email ON "tusers"("email");
CREATE INDEX IF NOT EXISTS idx_trequests_requested_by ON "trequests"("requested_by");
CREATE INDEX IF NOT EXISTS idx_trequests_status ON "trequests"("status");
CREATE INDEX IF NOT EXISTS idx_tpayments_request_id ON "tpayments"("request_id");
CREATE INDEX IF NOT EXISTS idx_tannouncements_priest ON "tannouncements"("assigned_priest");
CREATE INDEX IF NOT EXISTS idx_tnotifications_type ON "tnotifications"("type");

-- Add updated_at trigger function (PostgreSQL specific)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for auto-updating updated_at columns
CREATE TRIGGER update_tusers_updated_at BEFORE UPDATE ON "tusers" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tpriests_updated_at BEFORE UPDATE ON "tpriests" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tdonations_updated_at BEFORE UPDATE ON "tdonations" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tmail_updated_at BEFORE UPDATE ON "tmail" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tdocuments_updated_at BEFORE UPDATE ON "tdocuments" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tcertificate_types_updated_at BEFORE UPDATE ON "tcertificate_types" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tcertificate_details_updated_at BEFORE UPDATE ON "tcertificate_details" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_trequests_updated_at BEFORE UPDATE ON "trequests" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tpayments_updated_at BEFORE UPDATE ON "tpayments" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tannouncements_updated_at BEFORE UPDATE ON "tannouncements" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tnotifications_updated_at BEFORE UPDATE ON "tnotifications" FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();