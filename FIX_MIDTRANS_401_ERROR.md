# 🔑 MIDTRANS KEYS - Complete Setup Guide

## Current Status
```
❌ FAIL - Keys masih DUMMY placeholder
MIDTRANS_SERVER_KEY=SB-Mid-server-ABC123XYZ789
MIDTRANS_CLIENT_KEY=SB-Mid-client-ABC123XYZ789
```

**Hasil**: Midtrans return HTTP 401 Unauthorized

---

## ✅ Solution: Get Real Keys

### Method 1: Manual Setup (Recommended)

**Duration**: 5-10 menit

#### Step 1: Register Sandbox Account
```
1. Go to: https://sandbox.midtrans.com
2. Click "Sign Up" (atas kanan)
3. Fill form:
   - Email: your@email.com
   - Password: your_password
   - Confirm password
4. Click "Sign Up"
5. Check email untuk verify link
6. Click verify link
7. Login kembali
```

#### Step 2: Get Access Keys
```
Setelah login:
1. Click Settings icon (⚙️) di menu kiri
2. Click "Access Keys"
3. Anda akan melihat:
   - Server Key: SB-Mid-server-XXXXXXXXXXXX
   - Client Key: SB-Mid-client-XXXXXXXXXXXX
4. Copy both keys (select all, Ctrl+C)
```

✅ **IMPORTANT**: Keys are LONG strings (30+ characters), make sure copy semua!

#### Step 3: Update .env File
```bash
# Method A: Edit dengan text editor
vim .env
# atau
nano .env
# atau buka dengan VS Code

Cari baris:
MIDTRANS_SERVER_KEY=SB-Mid-server-ABC123XYZ789

Ganti dengan:
MIDTRANS_SERVER_KEY=SB-Mid-server-[PASTE_SERVER_KEY_HERE]

Lalu cari:
MIDTRANS_CLIENT_KEY=SB-Mid-client-ABC123XYZ789

Ganti dengan:
MIDTRANS_CLIENT_KEY=SB-Mid-client-[PASTE_CLIENT_KEY_HERE]

Save file (Ctrl+S)
```

#### Step 4: Clear Cache & Restart
```bash
cd c:\TA\Padel\padel-booking

# Clear cache
php artisan config:clear

# Restart server (stop & start again)
# Ctrl+C (stop current server)
# php artisan serve --host=127.0.0.1 --port=8000
```

#### Step 5: Test Payment
```
1. Go to: http://127.0.0.1:8000/booking/10/detail
2. Click "Bayar Sekarang"
3. Select "Pembayaran Penuh"
4. Should see Snap popup ✅ (not error)
5. Select QRIS
6. Use test card:
   - Number: 4811 1111 1111 1114
   - Exp: 12/25
   - CVV: 123
7. Click "Confirm"
```

---

### Method 2: Using VS Code (Easiest)

```
1. Open VS Code
2. File → Open File → .env
3. Find: MIDTRANS_SERVER_KEY=
4. Replace value with real key
5. Find: MIDTRANS_CLIENT_KEY=
6. Replace value with real key
7. Save (Ctrl+S)
8. Open Terminal → Run: php artisan config:clear
9. Test payment
```

---

## 🔍 Verify Keys are Correct

### Check 1: View .env
```bash
cat .env | grep MIDTRANS
```

Should show:
```
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-[YOUR_KEY_30+_CHARS]
MIDTRANS_CLIENT_KEY=SB-Mid-client-[YOUR_KEY_30+_CHARS]
```

NOT:
```
MIDTRANS_SERVER_KEY=SB-Mid-server-ABC123XYZ789  ← Still dummy!
```

### Check 2: Test via Laravel Tinker
```bash
php artisan tinker
```

Then paste:
```php
echo config('midtrans.server_key');
echo "\n";
echo config('midtrans.client_key');
```

Should show YOUR actual keys, not "ABC123XYZ789"

---

## ⚠️ Common Mistakes

### ❌ Mistake 1: Keys not copied fully
```
WRONG:
MIDTRANS_SERVER_KEY=SB-Mid-server-4ZZfjFWZPvyTfL

CORRECT:
MIDTRANS_SERVER_KEY=SB-Mid-server-4ZZfjFWZPvyTfLFP5PWaHSMQ
```
→ Make sure copy the FULL key (30+ characters)

### ❌ Mistake 2: Forgot to clear cache
```bash
# Always run after updating .env
php artisan config:clear
# Then restart server
```

### ❌ Mistake 3: Using production keys in sandbox
```
WRONG (using production format):
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=Mid-server-xxxxx  ← No "SB-" prefix

CORRECT (sandbox format):
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx  ← With "SB-" prefix
```

### ❌ Mistake 4: Quotes or spaces
```
WRONG:
MIDTRANS_SERVER_KEY="SB-Mid-server-xxx"  ← Extra quotes
MIDTRANS_SERVER_KEY=SB-Mid-server-xxx   ← Extra space

CORRECT:
MIDTRANS_SERVER_KEY=SB-Mid-server-xxx
```

---

## 🧪 Troubleshooting

### Error: Still getting 401 Unauthorized after keys updated

**Checklist**:
```
□ Did you copy FULL key (not truncated)?
□ Did you clear cache (php artisan config:clear)?
□ Did you restart server?
□ Keys format correct (SB-Mid-server-xxxx, SB-Mid-client-xxxx)?
□ No extra quotes or spaces in .env?
```

### Error: Keys look correct but still fail

**Try this**:
```bash
# 1. Stop server (Ctrl+C)
# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Restart server
php artisan serve --host=127.0.0.1 --port=8000

# 4. Test again in browser
# 5. Open F12 → Console and look for new errors
```

### Error: Can't find Access Keys in dashboard

**Check**:
```
1. Are you logged in? (Check top right corner)
2. Is it Sandbox dashboard? (URL should have "sandbox.midtrans.com")
3. Click Settings (⚙️ icon) → Check menu structure
4. If still can't find:
   - Logout & login again
   - Try different browser
   - Check if account is verified
```

---

## 📞 Getting Help

If still stuck:
```
1. Screenshot your .env (hide sensitive parts)
2. Check Midtrans dashboard screenshot
3. Screenshot error message from browser
4. Provide booking ID you tested with

Then ask for help with these details!
```

---

## 🎯 Summary

```
DO THIS NOW:
1. Open https://sandbox.midtrans.com
2. Get real Server & Client keys
3. Update .env with real keys
4. Run: php artisan config:clear
5. Restart server
6. Test payment again!

Takes ~5 minutes ⏱️
```

**Questions or stuck? Let me know!** 🚀
