<?php
//=====================================================START SCRIPT====================//
/**
 * KlikQRIS Activation Promo Modal
 * Displays an interactive popup modal to tenants on login for KlikQRIS activation.
 */

$tenant_id_promo = function_exists('get_current_tenant_id') ? get_current_tenant_id() : 1;
?>

<!-- Modal Iklan / Aktivasi KlikQRIS -->
<div class="modal fade" id="modalKlikqrisPromo" tabindex="-1" role="dialog" aria-labelledby="modalKlikqrisPromoLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content bd-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            <!-- Header dengan Gradient Mewah -->
            <div class="modal-header pd-y-20 pd-x-25 text-white position-relative" style="background: linear-gradient(135deg, #008080 0%, #10b759 100%); border-bottom: none;">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white text-success mg-r-15" style="width: 48px; height: 48px; font-size: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i class="fa fa-qrcode"></i>
                    </span>
                    <div>
                        <span class="badge badge-warning tx-11 font-weight-bold pd-y-3 pd-x-8 text-dark mb-1">
                            <i class="fa fa-bolt mg-r-3"></i> FITUR UNGGULAN MIKBOTAM
                        </span>
                        <h5 class="modal-title font-weight-bold tx-white mg-b-0" id="modalKlikqrisPromoLabel">
                            Aktivasi Pembayaran QRIS Otomatis 24 Jam
                        </h5>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; text-shadow: none; font-size: 28px; padding: 15px 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="modal-body pd-25 bg-white">
                <div class="text-center mg-b-20">
                    <h6 class="tx-gray-800 font-weight-bold tx-18 mg-b-8">
                        Tingkatkan Penjualan Voucher & Tagihan PPP Anda Sekarang!
                    </h6>
                    <p class="text-muted tx-14 mg-b-0" style="max-width: 620px; margin: 0 auto;">
                        Integrasikan bot Telegram Anda dengan payment gateway <strong>KlikQRIS</strong> untuk melayani deposit saldo dan pembayaran tagihan pelanggan secara <strong>Real-Time & Otomatis</strong> tanpa perlu cek mutasi manual.
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="row row-sm mg-b-20">
                    <div class="col-sm-6 mg-b-10">
                        <div class="pd-15 bd rounded bg-light d-flex align-items-start h-100" style="border-color: #e2e8f0 !important;">
                            <i class="fa fa-bolt tx-24 text-success mg-r-12 mg-t-2"></i>
                            <div>
                                <h6 class="tx-14 font-weight-bold tx-gray-800 mg-b-3">Top Up Saldo Otomatis</h6>
                                <p class="tx-12 text-muted mg-b-0">Saldo reseller langsung terisi otomatis seketika setelah QRIS dibayar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mg-b-10">
                        <div class="pd-15 bd rounded bg-light d-flex align-items-start h-100" style="border-color: #e2e8f0 !important;">
                            <i class="fa fa-credit-card tx-22 text-primary mg-r-12 mg-t-2"></i>
                            <div>
                                <h6 class="tx-14 font-weight-bold tx-gray-800 mg-b-3">Semua Bank & e-Wallet</h6>
                                <p class="tx-12 text-muted mg-b-0">BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay, & LinkAja.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mg-b-10 mg-sm-b-0">
                        <div class="pd-15 bd rounded bg-light d-flex align-items-start h-100" style="border-color: #e2e8f0 !important;">
                            <i class="fa fa-shield tx-22 text-warning mg-r-12 mg-t-2"></i>
                            <div>
                                <h6 class="tx-14 font-weight-bold tx-gray-800 mg-b-3">Aman & Terverifikasi</h6>
                                <p class="tx-12 text-muted mg-b-0">Proteksi double-signature webhook untuk keamanan transaksi Anda.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="pd-15 bd rounded bg-light d-flex align-items-start h-100" style="border-color: #e2e8f0 !important;">
                            <i class="fa fa-clock-o tx-22 text-info mg-r-12 mg-t-2"></i>
                            <div>
                                <h6 class="tx-14 font-weight-bold tx-gray-800 mg-b-3">Pendaftaran Instan</h6>
                                <p class="tx-12 text-muted mg-b-0">Daftar akun KlikQRIS gratis dan langsung dapatkan API Key & ID Merchant.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banner Call to Action Box -->
                <div class="pd-15 rounded text-center" style="background: #f0fdf4; border: 1px dashed #22c55e;">
                    <p class="tx-13 font-weight-bold text-success mg-b-3">
                        <i class="fa fa-gift mg-r-5"></i> Dapatkan Akses Integrasi Payment Gateway KlikQRIS Sekarang!
                    </p>
                    <p class="tx-12 text-muted mg-b-0">
                        Klik tombol di bawah ini untuk membuka formulir pendaftaran resmi merchant KlikQRIS.
                    </p>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="modal-footer bg-gray-100 pd-y-15 pd-x-25 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary tx-13 font-weight-bold" data-dismiss="modal">
                    <i class="fa fa-times mg-r-5"></i> Nanti Saja
                </button>
                <div>
                    <a href="./?Mikbotam=klikqris_settings" class="btn btn-outline-primary tx-13 font-weight-bold mg-r-5">
                        <i class="fa fa-cog mg-r-5"></i> Buka Pengaturan
                    </a>
                    <a href="https://klikqris.com/r/178014423787" target="_blank" class="btn btn-success tx-13 font-weight-bold shadow-sm" onclick="$('#modalKlikqrisPromo').modal('hide');">
                        <i class="fa fa-external-link mg-r-5"></i> Daftar & Aktivasi KlikQRIS
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    // Tampilkan popup iklan aktivasi KlikQRIS saat login / buka sesi baru
    try {
        var promoKey = 'mikbotam_klikqris_promo_session';
        if (!sessionStorage.getItem(promoKey)) {
            setTimeout(function() {
                if (typeof $ !== 'undefined' && $('#modalKlikqrisPromo').length) {
                    $('#modalKlikqrisPromo').modal('show');
                    sessionStorage.setItem(promoKey, '1');
                }
            }, 1000);
        }
    } catch(e) {
        console.error(e);
    }
})();
</script>
