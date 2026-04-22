#!/bin/bash

# Test Midtrans Configuration

echo "🔧 Midtrans Configuration Quick Test"
echo "===================================="
echo ""

# Check if .env exists and has keys
echo "📋 Checking .env configuration..."
if [ -f ".env" ]; then
    SERVER_KEY=$(grep "MIDTRANS_SERVER_KEY=" .env | cut -d '=' -f2)
    CLIENT_KEY=$(grep "MIDTRANS_CLIENT_KEY=" .env | cut -d '=' -f2)
    
    echo "Server Key set: ✓ (length: ${#SERVER_KEY})"
    echo "Client Key set: ✓ (length: ${#CLIENT_KEY})"
    
    # Check if keys are dummy
    if [[ "$SERVER_KEY" == *"ABC123"* ]] || [[ "$SERVER_KEY" == *"DUMMY"* ]]; then
        echo ""
        echo "⚠️  WARNING: Keys are DUMMY placeholders!"
        echo "   You need REAL keys from Midtrans dashboard"
        echo ""
        echo "Get keys here:"
        echo "  1. Go to https://sandbox.midtrans.com"
        echo "  2. Sign up / Login"
        echo "  3. Settings → Access Keys"
        echo "  4. Copy Server Key and Client Key"
        echo "  5. Update .env with real keys"
    fi
else
    echo "❌ .env file not found!"
fi

echo ""
echo "To test Midtrans connection, run:"
echo "  php artisan tinker"
echo ""
echo "Then paste this:"
echo '  $service = new \App\Services\MidtransService();'
echo '  $result = $service->createTransaction('
echo '      bookingId: 1,'
echo '      amount: 100000,'
echo '      paymentType: "full",'
echo '      customerData: ["name" => "Test", "email" => "test@example.com", "phone" => "081234567890"]'
echo '  );'
echo '  dd($result);'
echo ""
