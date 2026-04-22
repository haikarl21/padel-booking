@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fas fa-dollar-sign"></i> Manajemen Pembayaran
            </h2>
            <p class="text-muted" >Verifikasi dan approve/reject pembayaran dari user</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="badge bg-danger p-2 fs-6">
                <i class="fas fa-exclamation-circle"></i>
                {{ $pendingPayments->count() }} Menunggu Approve
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-danger mb-2">{{ $pendingPayments->count() }}</h3>
                    <p class="text-muted mb-0">Menunggu Verifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-success mb-2">{{ $allPayments->where('status', 'paid')->count() }}</h3>
                    <p class="text-muted mb-0">Sudah Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-2">{{ $allPayments->where('status', 'paid_pending_verification')->count() }}</h3>
                    <p class="text-muted mb-0">Menunggu Verifikasi Final</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-secondary mb-2">{{ $allPayments->where('status', 'rejected')->count() }}</h3>
                    <p class="text-muted mb-0">Ditolak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembayaran Pending Approval -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="mb-0">
                <i class="fas fa-hourglass-half"></i> Pembayaran Menunggu Approve
                <span class="badge bg-danger">{{ $pendingPayments->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($pendingPayments->isEmpty())
                <div class="alert alert-success m-3 mb-0">
                    <i class="fas fa-check-circle"></i> Semua pembayaran sudah diverifikasi
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pemesan</th>
                                <th>Lapangan & Tanggal</th>
                                <th>Nominal Unik</th>
                                <th>Metode</th>
                                <th>Konfirmasi</th>
                                <th>Waktu Transfer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $payment)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $payment->booking->booking_code }}</strong>
                                    </td>
                                    <td>
                                        {{ $payment->booking->customer_name }}<br>
                                        <small class="text-muted">{{ $payment->booking->phone }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $payment->booking->court->name }}<br>
                                            {{ $payment->booking->date->format('d/m/Y') }} 
                                            {{ $payment->booking->timeSlot->start_time }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="h6 mb-0">Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</span>
                                        <br>
                                        <small class="text-muted">
                                            Base: Rp {{ number_format($payment->amount, 0, ',', '.') }} 
                                            + {{ str_pad($payment->unique_code, 3, '0', STR_PAD_LEFT) }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $payment->getMethodDisplayName() ?? 'Bank Transfer' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($payment->confirmed_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Confirmed
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $payment->confirmed_at->format('H:i') }}</small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $payment->created_at->format('d/m H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-success" data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal{{ $payment->id }}"
                                                    title="Approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $payment->id }}"
                                                    title="Reject">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success bg-opacity-10">
                                                <h5 class="modal-title text-success">
                                                    <i class="fas fa-check-circle"></i> Approve Pembayaran
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Pastikan nominal transfer sudah masuk ke rekening kami!</strong></p>
                                                <div class="alert alert-info">
                                                    <p class="mb-1"><strong>{{ $payment->booking->booking_code }}</strong></p>
                                                    <p class="mb-1">{{ $payment->booking->customer_name }}</p>
                                                    <p class="mb-0"><strong>Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form method="POST" action="{{ route('payment.approve', $payment) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger bg-opacity-10">
                                                <h5 class="modal-title text-danger">
                                                    <i class="fas fa-times-circle"></i> Reject Pembayaran
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('payment.reject', $payment) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-danger">
                                                        <p class="mb-1"><strong>{{ $payment->booking->booking_code }}</strong></p>
                                                        <p class="mb-1">{{ $payment->booking->customer_name }}</p>
                                                        <p class="mb-0"><strong>Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</strong></p>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" 
                                                                  rows="3" placeholder="Contoh: Nominal tidak sesuai, transfernya belum masuk..." required></textarea>
                                                        @error('reason')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            @if($allPayments->isEmpty())
                <div class="alert alert-secondary m-3 mb-0">Belum ada data pembayaran</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pemesan</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allPayments as $payment)
                                <tr>
                                    <td><strong>{{ $payment->booking->booking_code }}</strong></td>
                                    <td>{{ $payment->booking->customer_name }}</td>
                                    <td>Rp {{ number_format($payment->total_unique, 0, ',', '.') }}</td>
                                    <td>
                                        @if($payment->status === 'paid')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($payment->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif($payment->status === 'expired')
                                            <span class="badge bg-secondary">Expired</span>
                                        @elseif($payment->status === 'waiting_verification')
                                            <span class="badge bg-warning text-dark">Pending Verify</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @if($payment->status === 'paid')
                                                Approved oleh {{ $payment->approver->name ?? 'Admin' }}
                                            @elseif($payment->status === 'rejected')
                                                {{ substr($payment->rejection_reason, 0, 40) }}...
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $payment->updated_at->format('d/m H:i') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
