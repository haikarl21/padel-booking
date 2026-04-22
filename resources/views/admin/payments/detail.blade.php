@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Back Button -->
            <a href="{{ route('admin.payments') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pembayaran
            </a>

            <!-- Booking Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">📋 Detail Verifikasi Pembayaran</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Kode Booking:</strong>
                                <span class="badge bg-primary">{{ $booking->booking_code }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Nama Customer:</strong> {{ $booking->customer_name }}
                            </p>
                            <p class="mb-2">
                                <strong>No. HP:</strong> {{ $booking->phone }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Tanggal Booking:</strong> {{ $booking->date->format('d/m/Y') }}
                            </p>
                            <p class="mb-2">
                                <strong>Status Booking:</strong>
                                @if($booking->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Payment Info -->
                    <h5 class="mb-3">💳 Informasi Pembayaran</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Total Harga Booking</p>
                            <p class="h6 mb-3">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>

                            <p class="text-muted mb-1">Kode Unik</p>
                            <p class="h6 mb-3 font-monospace">
                                <strong>{{ str_pad($payment->unique_code, 3, '0', STR_PAD_LEFT) }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Nominal yang Harus Dibayar</p>
                            <p class="h5 mb-3 text-primary">
                                <strong>Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</strong>
                            </p>

                            <p class="text-muted mb-1">Status Pembayaran</p>
                            <p class="h6 mb-0">
                                @if($payment->status === 'pending')
                                    <span class="badge bg-warning">Pending Verifikasi</span>
                                @elseif($payment->status === 'paid')
                                    <span class="badge bg-success">Sudah Di-Approve</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bukti Transfer -->
            @if($payment->proof_file)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">📸 Bukti Transfer</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <!-- Image Preview -->
                            <img src="{{ Storage::url('payments/' . $payment->proof_file) }}" 
                                 alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 400px;">
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('payment.download-proof', $payment) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <strong>⚠️ Belum Ada Bukti Upload</strong>
                    <p class="mb-0">Customer belum melakukan upload bukti transfer.</p>
                </div>
            @endif

            <!-- Admin Actions - Only if Status is Pending -->
            @if($payment->status === 'pending')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">✅ Aksi Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Approve Button -->
                            <div class="col-md-6 mb-3 mb-md-0">
                                <form action="{{ route('payment.approve', $payment) }}" method="POST">
                                    @csrf
                                    <div class="alert alert-info mb-3">
                                        <p class="mb-2">
                                            <strong>📋 Checklist Verifikasi:</strong>
                                        </p>
                                        <ul class="mb-0 small">
                                            <li>Apakah nominal yang diterima = <strong>Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</strong>?</li>
                                            <li>Apakah tanggal transfer sesuai?</li>
                                            <li>Apakah bukti transfer jelas dan terverifikasi?</li>
                                        </ul>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100" 
                                            onclick="return confirm('Approve pembayaran ini? Booking akan langsung dikonfirmasi.')">
                                        <i class="fas fa-check-circle"></i> Approve Pembayaran
                                    </button>
                                </form>
                            </div>

                            <!-- Reject Button -->
                            <div class="col-md-6">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="fas fa-times-circle"></i> Reject Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Pembayaran Sudah Diproses -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        @if($payment->status === 'paid')
                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-3 mb-2">Pembayaran Sudah Di-Approve</h5>
                            <p class="text-muted mb-2">
                                Disetujui oleh: <strong>{{ $payment->approver->name ?? 'System' }}</strong>
                            </p>
                            <p class="text-muted mb-0">
                                Waktu: {{ $payment->paid_at->format('d/m/Y H:i') }}
                            </p>
                        @elseif($payment->status === 'rejected')
                            <i class="fas fa-times-circle text-danger" style="font-size: 2rem;"></i>
                            <h5 class="mt-3 mb-2">Pembayaran Sudah Di-Reject</h5>
                            <div class="alert alert-light border mt-3 mb-0">
                                <strong>Alasan:</strong>
                                <p>{{ $payment->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Transaction History -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📝 Riwayat Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="mb-1"><strong>Pembayaran Dibuat</strong></p>
                                <small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>

                        @if($payment->updated_at !== $payment->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker" style="background-color: #6c757d;"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Lakuan Terakhir</strong></p>
                                    <small class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($payment->proof_file)
                            <div class="timeline-item">
                                <div class="timeline-marker" style="background-color: #0d6efd;"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Bukti Transfer Diupload</strong></p>
                                    <small class="text-muted">{{ isset($lastUpload) ? $lastUpload->format('d/m/Y H:i') : '-' }}</small>
                                </div>
                            </div>
                        @endif

                        @if($payment->status === 'paid')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Pembayaran Di-Approve</strong></p>
                                    <small class="text-muted">{{ $payment->paid_at->format('d/m/Y H:i') }} oleh {{ $payment->approver->name ?? 'System' }}</small>
                                </div>
                            </div>
                        @elseif($payment->status === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Pembayaran Di-Reject</strong></p>
                                    <small class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payment.reject', $payment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">🚫 Reject Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="4" placeholder="Jelaskan alasan penolakan pembayaran..." required></textarea>
                        <small class="text-muted d-block mt-2">Minimal 10 karakter</small>
                        @error('reason')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 30px;
        position: relative;
    }

    .timeline-marker {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-top: 5px;
        margin-right: 20px;
        flex-shrink: 0;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #dee2e6;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 35px;
        width: 1px;
        height: 30px;
        background: #dee2e6;
    }
</style>
@endsection
