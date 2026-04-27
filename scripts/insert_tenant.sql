USE meatshop_pos;
INSERT INTO tenants (tenant_id,domain,db_name,business_name,business_email,business_phone,business_address,subscription,plan,settings,`usage`,`limits`,status,payment_status,created_at)
VALUES ('27ad731a-050b-41d2-b4bd-714d87747751','fresh.local','tenant_0c3565cd142b','Fresh Shop','owner@fresh.local','','{}','{}','basic','{}','{}','{}','active','paid',NOW());
SELECT id,tenant_id,domain,db_name,business_name,business_email FROM tenants WHERE tenant_id='27ad731a-050b-41d2-b4bd-714d87747751';
