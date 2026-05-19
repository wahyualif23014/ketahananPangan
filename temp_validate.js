
    function kelolaLahan() {
        return {
            periodMode: @json(($filters['start'] || $filters['end']) ? 'tanggal' : 'semua'),
            searchQuery: @json($filters['search'] ?? ''),
            selectedResor: @json($filters['resor'] ?? ''),
            selectedSektor: @json($filters['sektor'] ?? ''),
            selectedJenis: @json($filters['jenis'] ?? ''),
            selectedKomoditi: @json($filters['komoditi'] ?? ''),
            kategoriProduksi: @json($filters['kategori'] ?? 'semua'),
            openResors: [],
            activeHistory: null,

            toggleHistory(id) {
                if (this.activeHistory === id) {
                    this.activeHistory = null;
                } else {
                    this.activeHistory = id;
                }
            },

            // Production Flow State (Real)
            activeLahan: null,
            isEditMode: false,
            activeProcessId: null,
            activeTanamId: null,
            modalTanam: false,
            modalPanen: false,
            modalSerapan: false,
            modalValidasi: false,
            validasiData: { tanam: [], panen: [], serapan: [], has_active: false },
            lahanStages: @json($lahanStagesMap ?? new stdClass()),

            // Tolak Validasi State
            tolakModalData: {
                isOpen: false,
                type: '', // 'tanam', 'panen', 'serapan'
                id: null,
                lahanInfo: null,
                alasan: '',
                targetReject: 'tanam'
            },
            
            // Detail Modal State
            detailModalData: {
                isOpen: false,
                type: '',
                data: null
            },

            // Form Data
            formTanam: {
                tgl_tanam: '{{ date('Y-m-d') }}',
                luas_tanam: 0,
                jenis_bibit: '',
                kebutuhan_bibit: '',
                est_awal_panen: '{{ date('Y-m-d') }}',
                est_akhir_panen: '{{ date('Y-m-d') }}',
                keterangan_tanam: ''
            },
            formPanen: {
                tgl_panen: '{{ date('Y-m-d') }}',
                luas_panen: 0,
                status_panen: 1, // 1: normal, 2: gagal, 3: dini, 4: tebasan
                total_panen: 0,
                keterangan_panen: ''
            },
            formSerapan: {
                tgl_distribusi: '{{ date('Y-m-d') }}',
                total_distribusi: 0,
                distribusi_ke: 1, // 1: bulog, 2: pabrik, 3: tengkulak, 4: konsumsi sendiri
                keterangan_serapan: ''
            },

            init() {
                // Initialize all resors as open by default
                @foreach($data as $resor)
                    this.openResors.push('{{ str_replace('.', '_', $resor->id_tingkat) }}');
                @endforeach
            },

            get sisaLahan() {
                if (!this.activeLahan) return 0;
                let maxLahan = parseFloat(this.activeLahan.luas_lahan || 0);
                let terpakai = 0;
                if (this.activeLahan.history_tanam && this.activeLahan.history_tanam.length > 0) {
                    terpakai = this.activeLahan.history_tanam.reduce((sum, t) => {
                        if (this.isEditMode && t.id_tanam === this.activeProcessId) return sum;
                        return sum + parseFloat(t.luas_tanam || 0);
                    }, 0);
                } else if (this.activeLahan.luas_tanam && !this.isEditMode) {
                    terpakai = parseFloat(this.activeLahan.luas_tanam || 0);
                }
                return Math.max(0, maxLahan - terpakai);
            },

            openTolakModal(id, type, label) {
                this.tolakModalData.type = type;
                this.tolakModalData.lahanInfo = { nama_wilayah: label };
                this.tolakModalData.alasan = '';
                this.tolakModalData.targetReject = 'tanam';
                this.tolakModalData.id = id;
                this.tolakModalData.isOpen = true;
            },

            openDetailModal(type, data) {
                this.detailModalData.type = type;
                this.detailModalData.data = data;
                this.detailModalData.isOpen = true;
            },

            async submitTolak() {
                if (!this.tolakModalData.alasan.trim()) {
                    $notify('error', 'Alasan Wajib Diisi', 'Harap masukkan alasan penolakan sebelum melanjutkan!');
                    return;
                }

                const url = `/admin/kelola-lahan/${this.tolakModalData.type}/${this.tolakModalData.id}/tolak`;

                try {
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            alasan: this.tolakModalData.alasan,
                            target_reject: this.tolakModalData.targetReject
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.tolakModalData.isOpen = false;
                        $notify('success', 'Validasi Ditolak', data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        $notify('error', 'Gagal Menolak', data.message || 'Terjadi kesalahan.');
                    }
                } catch (err) {
                    console.error(err);
                    $notify('error', 'Kesalahan Koneksi', 'Terjadi kesalahan saat memproses permintaan.');
                }
            },

            openStageModal(id_lahan, rowData, forcedStage = null, targetTanamId = null) {
                this.activeLahan = rowData;
                this.isEditMode = false;
                this.activeTanamId = targetTanamId;
                const stage = forcedStage !== null ? forcedStage : this.lahanStages[id_lahan];
                if (stage === 0) {
                    this.formTanam.luas_tanam = '';
                    this.formTanam.tgl_tanam = '';
                    this.formTanam.jenis_bibit = '';
                    this.formTanam.kebutuhan_bibit = '';
                    this.formTanam.est_awal_panen = '';
                    this.formTanam.est_akhir_panen = '';
                    this.formTanam.keterangan_tanam = '';
                    this.modalTanam = true;
                } else if (stage === 1) {
                    this.formPanen.luas_panen = rowData.luas_tanam || rowData.luas_lahan;
                    this.modalPanen = true;
                } else if (stage === 2) {
                    this.formSerapan.total_distribusi = rowData.total_panen || 0;
                    this.modalSerapan = true;
                }
            },

            editTanam(id_tanam, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_tanam;
                this.formTanam = {
                    tgl_tanam: rowData.tgl_tanam,
                    luas_tanam: rowData.luas_tanam,
                    jenis_bibit: rowData.nama_bibit || '',
                    kebutuhan_bibit: rowData.kebutuhan_bibit || '',
                    est_awal_panen: rowData.est_awal_panen,
                    est_akhir_panen: rowData.est_akhir_panen,
                    keterangan_tanam: rowData.keterangan_tanam || ''
                };
                this.modalTanam = true;
            },

            editPanen(id_panen, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_panen;
                this.formPanen = {
                    tgl_panen: rowData.tgl_panen,
                    luas_panen: rowData.luas_panen,
                    status_panen: rowData.status_panen || 1,
                    total_panen: rowData.total_panen || 0,
                    keterangan_panen: rowData.ket_panen || ''
                };
                this.modalPanen = true;
            },

            editSerapan(id_distribusi, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_distribusi;
                this.formSerapan = {
                    tgl_distribusi: rowData.tgl_distribusi,
                    total_distribusi: rowData.total_distribusi || 0,
                    distribusi_ke: rowData.distribusi_ke || 1,
                    keterangan_serapan: rowData.keterangan_distribusi || ''
                };
                this.modalSerapan = true;
            },

            async submitTanam() {
                // Frontend Validation: Luas Tanam
                let sisa = this.sisaLahan;
                let inputLuas = parseFloat(this.formTanam.luas_tanam || 0);
                if (inputLuas > sisa) {
                    $notify('warning', 'Validasi Gagal', `Luas tanam (${inputLuas} Ha) tidak boleh melebihi sisa potensi lahan (${sisa.toFixed(2)} Ha).`);
                    return;
                }

                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/tanam/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.tanam.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            ...this.formTanam
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalTanam = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 1;
                        $notify('success', 'Tanam Berhasil Dicatat', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        $notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                    }
                } catch (error) {
                    $notify('error', 'Kesalahan Koneksi', error.message);
                }
            },

            async submitPanen() {
                // Frontend Validation: Luas Panen <= Luas Tanam
                let inputLuasPanen = parseFloat(this.formPanen.luas_panen || 0);
                let maxTanam = 0;
                
                // Cari luas tanam yang sedang aktif (dari form data activeLahan)
                if (this.activeLahan) {
                    if (this.isEditMode) {
                         // Saat edit panen, activeLahan sebenarnya adalah data gabungan row, tanam, panen
                         maxTanam = parseFloat(this.activeLahan.luas_tanam || 0);
                    } else if (this.activeLahan.luas_tanam) {
                         maxTanam = parseFloat(this.activeLahan.luas_tanam);
                    }
                }
                
                // Pastikan status panen != 2 (Gagal) karena kalau gagal otomatis 0
                if (this.formPanen.status_panen != 2 && maxTanam > 0 && inputLuasPanen > maxTanam) {
                    $notify('warning', 'Validasi Gagal', `Luas panen (${inputLuasPanen} Ha) tidak boleh melebihi luas tanam (${maxTanam} Ha).`);
                    return;
                }

                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/panen/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.panen.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            id_tanam: this.activeTanamId,
                            ...this.formPanen
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalPanen = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 2;
                        $notify('success', 'Panen Berhasil Dicatat', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        $notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                    }
                } catch (error) {
                    $notify('error', 'Kesalahan Koneksi', error.message);
                }
            },

            async submitSerapan() {
                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/serapan/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.serapan.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            id_tanam: this.activeTanamId,
                            ...this.formSerapan
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalSerapan = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 0;
                        $notify('success', 'Serapan Berhasil Dicatat', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        $notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                    }
                } catch (error) {
                    $notify('error', 'Kesalahan Koneksi', error.message);
                }
            },

            async deleteTanam(id) {
                const ok = await $confirm({ type: 'danger', title: 'Hapus Data Tanam?', message: 'Seluruh data panen & serapan terkait juga akan ikut dihapus. Tindakan ini tidak dapat dibatalkan.', confirmText: 'Ya, Hapus Semua' });
                if (!ok) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/tanam/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        $notify('success', 'Data Tanam Dihapus', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else $notify('error', 'Gagal Menghapus', result.message);
                } catch (e) { $notify('error', 'Kesalahan', e.message); }
            },

            async deletePanen(id) {
                const ok = await $confirm({ type: 'danger', title: 'Hapus Data Panen?', message: 'Data serapan terkait juga akan ikut dihapus.', confirmText: 'Ya, Hapus' });
                if (!ok) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/panen/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        $notify('success', 'Data Panen Dihapus', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else $notify('error', 'Gagal Menghapus', result.message);
                } catch (e) { $notify('error', 'Kesalahan', e.message); }
            },

            async deleteSerapan(id) {
                const ok = await $confirm({ type: 'danger', title: 'Hapus Data Serapan?', message: 'Data serapan ini akan dihapus dari sistem secara permanen.', confirmText: 'Ya, Hapus' });
                if (!ok) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/serapan/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        $notify('success', 'Data Serapan Dihapus', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else $notify('error', 'Gagal Menghapus', result.message);
                } catch (e) { $notify('error', 'Kesalahan', e.message); }
            },

            async openValidasiModal(id_lahan, rowData) {
                this.activeLahan = rowData;
                try {
                    const response = await fetch(`/admin/kelola-lahan/lahan/${id_lahan}/validasi-data`);
                    const result = await response.json();
                    this.validasiData = result;
                    this.modalValidasi = true;
                } catch (error) {
                    $notify('error', 'Gagal Memuat Data', 'Gagal mengambil data validasi: ' + error.message);
                }
            },

            async submitValidasi() {
                const ok = await $confirm({ type: 'success', title: 'Selesai Siklus?', message: 'Siklus lahan ini akan diakhiri dan data kelola lahan akan diarsipkan. Lahan akan kosong kembali.', confirmText: 'Ya, Selesai Siklus' });
                if (!ok) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/lahan/${this.activeLahan.id_lahan}/validasi`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalValidasi = false;
                        $notify('success', 'Validasi Berhasil!', result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        $notify('error', 'Gagal Memvalidasi', result.message || 'Terjadi kesalahan server.');
                    }
                } catch (error) {
                    $notify('error', 'Kesalahan Koneksi', error.message);
                }
            },

            toggleResor(id) {
                if (this.openResors.includes(id)) {
                    this.openResors = this.openResors.filter(i => i !== id);
                } else {
                    this.openResors.push(id);
                }
            },

            isResorOpen(id) {
                return this.openResors.includes(id);
},



            submitFilters() {
                const url = new URL(window.location.href);
                const params = {
                    resor: this.selectedResor,
                    sektor: this.selectedSektor,
                    jenis: this.selectedJenis,
                    komoditi: this.selectedKomoditi,
                    kategori: this.kategoriProduksi,
                    search: this.searchQuery
                };
                
                if (this.periodMode === 'tanggal') {
                    params.start_date = document.getElementById('start_date').value;
                    params.end_date = document.getElementById('end_date').value;
                }

                Object.keys(params).forEach(key => {
                    if (params[key]) {
                        url.searchParams.set(key, params[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                
                if (this.periodMode === 'semua') {
                    url.searchParams.delete('start_date');
                    url.searchParams.delete('end_date');
                }

                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        };
    }
