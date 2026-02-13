# Notification Recovery Guide

## Overview
Sistem notifikasi menggunakan queue (Redis) untuk mengirim WhatsApp dan Email. Jika Redis down atau queue worker tidak berjalan, notifikasi tidak akan terkirim dan users tidak akan menerima e-ticket mereka.

---

## 🚨 Problem Detection

### Dashboard Indicators
1. **System Health Widget** (Dashboard Admin)
   - **Missing Notifications**: Menunjukkan jumlah payment verified yang belum mendapat notifikasi
   - **Redis Status**: Disconnected = Redis down
   - **Failed Jobs**: Job yang gagal setelah retry

2. **Check Manually via Database**
```sql
-- Cek payment verified tanpa notifikasi
SELECT COUNT(*) 
FROM registrations r 
WHERE r.status = 'payment_verified' 
AND NOT EXISTS (
  SELECT 1 FROM notification_logs nl 
  WHERE nl.registration_id = r.id 
  AND nl.type = 'payment_verified'
  AND nl.status = 'sent'
);
```

---

## 🔧 Recovery Solutions

### Solution 1: Via Admin Panel (Recommended)
**Untuk non-technical admin**

1. Login ke Admin Panel
2. Navigate to **System > Notification Logs**
3. Click **"Resend Missing Notifications"** button
4. Select notification type:
   - **Payment Verified (E-Ticket)** - untuk e-ticket delivery
   - **Payment Uploaded** - konfirmasi upload bukti
   - **Registration Created** - welcome message
5. Click **Submit**
6. Notifikasi akan di-queue dan dikirim otomatis

**Monitoring:**
- Check "System Health Widget" - "Missing Notifications" harus menjadi 0
- Check "Notification Logs" untuk status "sent"

---

### Solution 2: Via Command Line
**Untuk technical staff dengan SSH access**

#### Dry Run (Preview Only)
```bash
php artisan notifications:resend-missing --dry-run
```

#### Actually Send
```bash
# Resend payment_verified (e-tickets)
php artisan notifications:resend-missing

# Resend payment_uploaded
php artisan notifications:resend-missing --type=payment_uploaded

# Resend registration_created
php artisan notifications:resend-missing --type=registration_created
```

**Output Example:**
```
🔍 Searching for registrations missing 'payment_verified' notifications...

Found 11 registration(s) without sent 'payment_verified' notifications:

✅ REG-70014 - Queued for John Doe
✅ REG-83502 - Queued for Jane Smith
...

📊 Results:
   ✅ Queued: 11

✅ Done! Notifications have been queued.
💡 Tip: Run "php artisan queue:work --queue=notifications" if not running
```

---

## 🛡️ Prevention Strategies

### 1. Monitor Redis Health
**Setup Monitoring:**
```bash
# Check Redis status
redis-cli ping

# Monitor Redis memory
redis-cli info memory

# Check queue size
redis-cli llen queues:notifications
```

**Alerts:**
- Setup monitoring untuk Redis uptime
- Alert jika Redis down > 5 minutes
- Alert jika queue size > 1000 jobs

### 2. Keep Queue Workers Running
**Ensure queue worker always running:**

```bash
# Check if running
ps aux | grep "queue:work"

# Start if not running (production)
php artisan queue:work --queue=notifications --tries=3 --timeout=120 &

# Better: Use Supervisor (already configured)
sudo supervisorctl status
sudo supervisorctl restart laravel-worker:*
```

**Supervisor Configuration** (already exists at `/etc/supervisor/conf.d/laravel-worker.conf`):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=notifications --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

### 3. Use Laravel Horizon (Already Installed)
**Monitor via Horizon Dashboard:**
```bash
# Access at: https://your-domain.com/admin/horizon
# Login dengan admin credentials

# Start Horizon
php artisan horizon

# Or use Supervisor to manage Horizon
```

**Horizon Benefits:**
- Real-time queue monitoring
- Failed job retry dashboard
- Job throughput metrics
- Auto-balancing workers

### 4. Setup Database Queue as Backup
**Edit `.env` when Redis is unstable:**
```env
# Switch to database queue
QUEUE_CONNECTION=database

# After Redis fixed, switch back
QUEUE_CONNECTION=redis
```

**Run worker:**
```bash
php artisan queue:work --queue=notifications
```

---

## 📋 Regular Maintenance Checklist

### Daily
- [ ] Check "System Health Widget" di dashboard
- [ ] Verify "Missing Notifications" = 0
- [ ] Check "Failed Jobs" < 5

### Weekly
- [ ] Review Notification Logs untuk failure patterns
- [ ] Check Redis memory usage
- [ ] Review queue worker logs

### Monthly
- [ ] Clear old notification logs (>3 months)
```bash
php artisan db:prune --model=App\\Models\\NotificationLog
```
- [ ] Review and retry failed jobs
```bash
php artisan queue:retry all
```

---

## 🔍 Troubleshooting

### Issue: Redis Connection Failed
**Symptoms:**
- "Redis: Disconnected" di System Health Widget
- Queue tidak berjalan
- Notifikasi tidak terkirim

**Solutions:**
1. Check Redis service
```bash
sudo systemctl status redis
sudo systemctl restart redis
```

2. Check Redis configuration
```bash
# Edit /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

3. Verify `.env` settings
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Issue: Queue Worker Not Processing
**Symptoms:**
- Jobs stuck in queue
- Notification logs show "pending" status

**Solutions:**
1. Check worker status
```bash
sudo supervisorctl status laravel-worker:*
```

2. Restart workers
```bash
sudo supervisorctl restart laravel-worker:*
```

3. Clear failed jobs and retry
```bash
php artisan queue:failed
php artisan queue:retry all
```

### Issue: Failed Jobs Keep Increasing
**Symptoms:**
- "Failed Jobs" widget shows high count
- Specific registrations consistently fail

**Solutions:**
1. Check failed jobs details
```bash
php artisan queue:failed
```

2. Check specific error
```bash
php artisan queue:failed:show [job-id]
```

3. Common failures:
   - **Invalid phone number**: Update participant phone
   - **Email delivery failed**: Check SMTP settings
   - **Fonnte API error**: Check API token validity
   - **PDF generation failed**: Check storage permissions

4. Retry specific job
```bash
php artisan queue:retry [job-id]
```

---

## 🚀 Emergency Recovery Process

**When Redis was down and many notifications are missing:**

1. **Verify Redis is back online**
```bash
redis-cli ping
# Response: PONG
```

2. **Check extent of problem**
```bash
php artisan notifications:resend-missing --dry-run
```

3. **Notify users you're fixing it** (Optional)
   - Post announcement on social media
   - "We're resending e-tickets to affected users"

4. **Run recovery**
```bash
php artisan notifications:resend-missing
```

5. **Monitor progress**
   - Watch System Health Widget
   - Check Notification Logs resource
   - Verify users receive emails/WhatsApp

6. **Verify completion**
```bash
# Should return 0
php artisan notifications:resend-missing --dry-run
```

7. **Document incident**
   - When Redis went down
   - How many users affected
   - How long to recover
   - Steps taken
   - Prevention measures added

---

## 📞 Support Contacts

**For Technical Issues:**
- Developer: [Your Contact]
- Infrastructure: [Your Contact]

**For User Complaints:**
- Customer Service: [Your Contact]

---

## 📝 Logging & Audit

**All notification activities are logged in:**
1. `notification_logs` table - All sent/failed notifications
2. `failed_jobs` table - Jobs that failed after max retries
3. Laravel logs - `storage/logs/laravel.log`
4. Queue worker logs - `storage/logs/worker.log` (if configured)

**Query for audit:**
```sql
-- Get notifications sent in last 24h
SELECT * FROM notification_logs 
WHERE created_at >= NOW() - INTERVAL 1 DAY
ORDER BY created_at DESC;

-- Get failed notifications
SELECT * FROM notification_logs 
WHERE status = 'failed'
ORDER BY created_at DESC;
```

---

## ✅ Success Criteria

**After recovery, verify:**
- [ ] "Missing Notifications" widget shows 0
- [ ] All payment_verified registrations have sent notification
- [ ] Users report receiving e-tickets
- [ ] No new complaints about missing tickets
- [ ] Redis stable and connected
- [ ] Queue workers running properly
- [ ] Failed jobs cleared or resolved

---

**Last Updated:** 2026-02-13
**Version:** 1.0
