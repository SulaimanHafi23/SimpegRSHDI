<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = Illuminate\Support\Facades\DB::connection();
$dbName = $db->getDatabaseName();
$rows = Illuminate\Support\Facades\DB::select('SHOW TABLES');
$tables = [];
foreach ($rows as $r) {
    $arr = (array) $r;
    $tables[] = array_values($arr)[0];
}
sort($tables);

$kamus = [
    'attendance'=>'kehadiran','attendances'=>'kehadiran','photo'=>'foto','photos'=>'foto','audit'=>'audit','log'=>'log','logs'=>'log','business'=>'bisnis','trip'=>'perjalanan dinas','trips'=>'perjalanan dinas','cache'=>'tembolok','lock'=>'kunci','locks'=>'kunci','department'=>'departemen','departments'=>'departemen','document'=>'dokumen','documents'=>'dokumen','type'=>'tipe','types'=>'tipe','failed'=>'gagal','job'=>'pekerjaan','jobs'=>'pekerjaan','gender'=>'jenis kelamin','genders'=>'jenis kelamin','holiday'=>'hari libur','holidays'=>'hari libur','leave'=>'cuti','request'=>'pengajuan','requests'=>'pengajuan','location'=>'lokasi','locations'=>'lokasi','notification'=>'notifikasi','notifications'=>'notifikasi','overtime'=>'lembur','password'=>'kata sandi','reset'=>'reset','token'=>'token','tokens'=>'token','personal'=>'pribadi','access'=>'akses','religion'=>'agama','religions'=>'agama','salary'=>'gaji','component'=>'komponen','components'=>'komponen','session'=>'sesi','sessions'=>'sesi','shift'=>'shift','shifts'=>'shift','day'=>'hari','times'=>'waktu','time'=>'waktu','override'=>'penyesuaian','overrides'=>'penyesuaian','swap'=>'tukar','user'=>'pengguna','users'=>'pengguna','worker'=>'pegawai','workers'=>'pegawai','off'=>'libur','exception'=>'pengecualian','exceptions'=>'pengecualian','history'=>'riwayat','histories'=>'riwayat','role'=>'peran','roles'=>'peran','permission'=>'izin','permissions'=>'izin','model'=>'model','has'=>'memiliki','id'=>'id','name'=>'nama','code'=>'kode','description'=>'deskripsi','created'=>'dibuat','updated'=>'diperbarui','deleted'=>'dihapus','date'=>'tanggal','start'=>'mulai','end'=>'selesai','status'=>'status','reason'=>'alasan','approved'=>'disetujui','rejected'=>'ditolak','by'=>'oleh','at'=>'pada','from'=>'dari','to'=>'ke','is'=>'apakah','active'=>'aktif','taxable'=>'kena pajak','latitude'=>'lintang','longitude'=>'bujur','notes'=>'catatan','email'=>'email','address'=>'alamat','phone'=>'telepon','number'=>'nomor','birth'=>'lahir','place'=>'tempat','employment'=>'kepegawaian','resign'=>'resign','nip'=>'nip','parent'=>'induk','manager'=>'manajer','guard'=>'guard','old'=>'lama','new'=>'baru','ip'=>'ip','url'=>'url','metadata'=>'metadata','itinerary'=>'rencana perjalanan','accommodation'=>'akomodasi','transportation'=>'transportasi','destination'=>'tujuan','purpose'=>'tujuan','estimated'=>'estimasi','cost'=>'biaya','requires'=>'memerlukan','approval'=>'persetujuan','attachment'=>'lampiran','max'=>'maksimal','days'=>'hari','per'=>'per','year'=>'tahun','notice'=>'pemberitahuan','file'=>'file','format'=>'format','size'=>'ukuran','universal'=>'universal','national'=>'nasional','radius'=>'radius','enforce'=>'wajib','geofence'=>'geofence','total'=>'total','hours'=>'jam','grace'=>'toleransi','period'=>'periode','minutes'=>'menit','overnight'=>'lintas hari','check'=>'cek','in'=>'masuk','out'=>'keluar','late'=>'terlambat','early'=>'lebih awal','outside'=>'di luar','read'=>'dibaca','last'=>'terakhir','remember'=>'ingat','payload'=>'muatan','attempts'=>'percobaan','available'=>'tersedia','reserved'=>'dicadangkan','queue'=>'antrian','connection'=>'koneksi','migration'=>'migrasi','batch'=>'batch','notifiable'=>'yang dapat diberi notifikasi','tokenable'=>'pemilik token','abilities'=>'kemampuan','expires'=>'kedaluwarsa','effective'=>'berlaku','until'=>'sampai','changed'=>'diubah','change'=>'perubahan','requested'=>'diminta','executed'=>'dieksekusi','target'=>'target','requester'=>'peminta'
];

$terjemah = function (string $snake) use ($kamus): string {
    $parts = explode('_', strtolower($snake));
    $hasil = [];
    foreach ($parts as $p) {
        if (isset($kamus[$p])) {
            $hasil[] = $kamus[$p];
            continue;
        }
        if (str_ends_with($p, 's')) {
            $sing = substr($p, 0, -1);
            if (isset($kamus[$sing])) {
                $hasil[] = $kamus[$sing];
                continue;
            }
        }
        $hasil[] = str_replace('-', ' ', $p);
    }
    return ucwords(trim(implode(' ', $hasil)));
};

$out = [];
$out[] = 'DOKUMEN ENTITAS DAN ATRIBUT DATABASE';
$out[] = 'Bahasa: Indonesia';
$out[] = 'Database: ' . $dbName;
$out[] = 'Tanggal pembuatan: ' . date('Y-m-d H:i:s');
$out[] = 'Total entitas (tabel): ' . count($tables);
$out[] = '';

$entityNo = 0;
foreach ($tables as $table) {
    $entityNo++;
    $entityId = $terjemah($table);
    $out[] = $entityNo . '. Entitas: ' . $entityId . ' (tabel: ' . $table . ')';
    $out[] = '   Deskripsi: Menyimpan data untuk entitas ' . strtolower($entityId) . '.';

    $cols = Illuminate\Support\Facades\DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
    $out[] = '   Total atribut: ' . count($cols);

    $attrNo = 0;
    foreach ($cols as $c) {
        $attrNo++;
        $attrId = $terjemah($c->Field);
        $nullable = ($c->Null === 'YES') ? 'Ya' : 'Tidak';
        $kunci = ($c->Key !== '') ? $c->Key : '-';
        $default = is_null($c->Default) ? 'NULL' : (string) $c->Default;
        $komentar = ($c->Comment !== '') ? $c->Comment : '-';

        $out[] = '   - Atribut ' . $attrNo . ': ' . $attrId . ' (kolom: ' . $c->Field . ')';
        $out[] = '     Tipe data: ' . $c->Type;
        $out[] = '     Nullable: ' . $nullable;
        $out[] = '     Kunci: ' . $kunci;
        $out[] = '     Nilai bawaan: ' . $default;
        $out[] = '     Keterangan: ' . $komentar;
    }
    $out[] = '';
}

file_put_contents('docs/entitas-atribut-database.txt', implode(PHP_EOL, $out));
echo "BERHASIL\n";
