<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\SessionCheckTraits;

use App\Emp_data;
use App\Emp_dik;
use App\Emp_gol;
use App\Emp_jab;
use App\Fr_disposisi;
use App\Glo_dik;
use App\Glo_disposisi_kode;
use App\Glo_disposisi_penanganan;
use App\Glo_org_golongan;
use App\Glo_org_jabatan;
use App\Glo_org_kedemp;
use App\Glo_org_lokasi;
use App\Glo_org_statusemp;
use App\glo_org_unitkerja;
use App\Sec_access;
use App\Sec_menu;

session_start();

class DisposisiController extends Controller
{
	use SessionCheckTraits;

	public function __construct()
	{
		$this->middleware('auth');
	}


public function display_disposisi($no_form, $idtop, $level = 0)
	{
		// $query = Fr_disposisi::
		// 			leftJoin('bpaddtfake.dbo.emp_data as emp1', 'emp1.id_emp', '=', 'bpaddtfake.dbo.fr_disposisi.to_pm')
		// 			->where('no_form', $no_form)
		// 			->where('idtop', $idtop)
		// 			->orderBy('ids')
		// 			->get();

		$query = DB::select( DB::raw("SELECT * 
					from bpaddtfake.dbo.fr_disposisi
					left join bpaddtfake.dbo.emp_data on bpaddtfake.dbo.emp_data.id_emp = bpaddtfake.dbo.fr_disposisi.to_pm
					where no_form = '$no_form'
					and idtop = '$idtop'
					order by ids
					") );
		$query = json_decode(json_encode($query), true);

		$result = '';

		if (count($query) > 0) {
			foreach ($query as $log) {
				$padding = ($level * 20);

				$result .= '<tr >
								<td style="padding-left:'.$padding.'px; padding-top:10px">
									<i class="fa fa-user"></i> <span>'.$log['nrk_emp'].' '.ucwords(strtolower($log['nm_emp'])).'</span> 
									'.(($log['child'] == 0 && $log['rd'] == 'S') ? "<i data-toggle='tooltip' title='Sudah ditindaklanjut!' class='fa fa-check' style='color: blue'></i>" : '').'
									'.(($log['child'] == 0 && $log['rd'] != 'S') ? "<i data-toggle='tooltip' title='Belum ditindaklanjut!' class='fa fa-close' style='color: red'></i>" : '').'
									<br> 
									<span class="text-muted"> Penanganan: <b>'. ($log['penanganan'] ? $log['penanganan'] : '-' ) .'</b> 
																				'.($log['catatan'] ? '('.$log['catatan'].')' : '' ).'</span>
									<br>
								</td>
							</tr>';

				if ($log['child'] == 1) {
					$result .= $this->display_disposisi($no_form, $log['ids'], $level+1);
				}
			}
		}
		return $result;
	}
	// ---------ADMIN----------- //

	public function formdisposisi(Request $request)
	{
		$this->checkSessionTime();
		$currentpath = str_replace("%20", " ", $_SERVER['REQUEST_URI']);
		$currentpath = explode("?", $currentpath)[0];
		$thismenu = Sec_menu::where('urlnew', $currentpath)->first('ids');
		$access = $this->checkAccess($_SESSION['user_data']['idgroup'], $thismenu['ids']);

		if ($request->yearnow) {
			$yearnow = (int)$request->yearnow;
		} else {
			$yearnow = (int)date('Y');
		}

		if ($request->monthnow) {
			$monthnow = (int)$request->monthnow;
		} else {
			$monthnow = (int)date('m');
		}

		if ($request->signnow) {
			$signnow = $request->signnow;
		} else {
			$signnow = "=";
		}

		if ($request->searchnow) {
			$qsearchnow = "and (kd_surat = '".$request->searchnow."' or no_form = '".$request->searchnow."')";
		} else {
			$qsearchnow = "";
		}

		$idgroup = $_SESSION['user_data']['id_emp'];
		if (is_null($idgroup)) {
			$disposisisents = DB::select( DB::raw("SELECT TOP (50) [ids]
												  ,[sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[kd_unit]
												  ,[no_form]
												  ,[kd_surat]
												  ,[status_surat]
												  ,[idtop]
												  ,[tgl_masuk]
												  ,[usr_input]
												  ,[tgl_input]
												  ,[no_index]
												  ,[kode_disposisi]
												  ,[perihal]
												  ,[tgl_surat]
												  ,[no_surat]
												  ,[asal_surat]
												  ,[kepada_surat]
												  ,[sifat1_surat]
												  ,[sifat2_surat]
												  ,[ket_lain]
												  ,[nm_file]
												  ,[kepada]
												  ,[noid]
												  ,[penanganan]
												  ,[catatan]
												  ,[from_user]
												  ,[from_pm]
												  ,[to_user]
												  ,[to_pm]
												  ,[rd]
												  ,[usr_rd]
												  ,[tgl_rd]
												  ,[selesai]
												  ,[child]
												  ,[penanganan_final]
												  ,[catatan_final]
												  FROM [bpaddtfake].[dbo].[fr_disposisi]
												  where status_surat like 's'
												  $qsearchnow
												  and month(tgl_masuk) $signnow $monthnow
												  and year(tgl_masuk) $signnow $yearnow
												  and sts = 1
												  order by tgl_masuk desc, no_form desc"));
			$disposisidrafts = DB::select( DB::raw("SELECT TOP (50) [ids]
												  ,[sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[kd_unit]
												  ,[no_form]
												  ,[kd_surat]
												  ,[status_surat]
												  ,[idtop]
												  ,[tgl_masuk]
												  ,[usr_input]
												  ,[tgl_input]
												  ,[no_index]
												  ,[kode_disposisi]
												  ,[perihal]
												  ,[tgl_surat]
												  ,[no_surat]
												  ,[asal_surat]
												  ,[kepada_surat]
												  ,[sifat1_surat]
												  ,[sifat2_surat]
												  ,[ket_lain]
												  ,[nm_file]
												  ,[kepada]
												  ,[noid]
												  ,[penanganan]
												  ,[catatan]
												  ,[from_user]
												  ,[from_pm]
												  ,[to_user]
												  ,[to_pm]
												  ,[rd]
												  ,[usr_rd]
												  ,[tgl_rd]
												  ,[selesai]
												  ,[child]
												  ,[penanganan_final]
												  ,[catatan_final]
												  FROM [bpaddtfake].[dbo].[fr_disposisi]
												  where status_surat like 'd'
												  $qsearchnow
												  and month(tgl_masuk) $signnow $monthnow
												  and year(tgl_masuk) $signnow $yearnow
												  and sts = 1
												  order by tgl_masuk desc, no_form desc"));
			$disposisisents = json_decode(json_encode($disposisisents), true);
			$disposisidrafts = json_decode(json_encode($disposisidrafts), true);
		} else {
			$disposisisents = DB::select( DB::raw("SELECT TOP 50 [ids]
												  ,[sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[kd_unit]
												  ,[no_form]
												  ,[kd_surat]
												  ,[status_surat]
												  ,[idtop]
												  ,[tgl_masuk]
												  ,[usr_input]
												  ,[tgl_input]
												  ,[no_index]
												  ,[kode_disposisi]
												  ,[perihal]
												  ,[tgl_surat]
												  ,[no_surat]
												  ,[asal_surat]
												  ,[kepada_surat]
												  ,[sifat1_surat]
												  ,[sifat2_surat]
												  ,[ket_lain]
												  ,[nm_file]
												  ,[kepada]
												  ,[noid]
												  ,[penanganan]
												  ,[catatan]
												  ,[from_user]
												  ,[from_pm]
												  ,[to_user]
												  ,[to_pm]
												  ,[rd]
												  ,[usr_rd]
												  ,[tgl_rd]
												  ,[selesai]
												  ,[child]
												  ,[penanganan_final]
												  ,[catatan_final]
												  FROM [bpaddtfake].[dbo].[fr_disposisi]
												  where status_surat like 's'
												  $qsearchnow
												  and month(tgl_masuk) $signnow $monthnow
												  and year(tgl_masuk) $signnow $yearnow
												  and sts = 1
												  order by tgl_masuk desc, no_form desc"));
			$disposisidrafts = DB::select( DB::raw("SELECT TOP (50) [ids]
												  ,[sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[kd_unit]
												  ,[no_form]
												  ,[kd_surat]
												  ,[status_surat]
												  ,[idtop]
												  ,[tgl_masuk]
												  ,[usr_input]
												  ,[tgl_input]
												  ,[no_index]
												  ,[kode_disposisi]
												  ,[perihal]
												  ,[tgl_surat]
												  ,[no_surat]
												  ,[asal_surat]
												  ,[kepada_surat]
												  ,[sifat1_surat]
												  ,[sifat2_surat]
												  ,[ket_lain]
												  ,[nm_file]
												  ,[kepada]
												  ,[noid]
												  ,[penanganan]
												  ,[catatan]
												  ,[from_user]
												  ,[from_pm]
												  ,[to_user]
												  ,[to_pm]
												  ,[rd]
												  ,[usr_rd]
												  ,[tgl_rd]
												  ,[selesai]
												  ,[child]
												  ,[penanganan_final]
												  ,[catatan_final]
												  FROM [bpaddtfake].[dbo].[fr_disposisi]
												  where status_surat like 'd'
												  $qsearchnow
												  and month(tgl_masuk) $signnow $monthnow
												  and year(tgl_masuk) $signnow $yearnow
												  and sts = 1
												  order by tgl_masuk desc, no_form desc"));
			$disposisisents = json_decode(json_encode($disposisisents), true);
			$disposisidrafts = json_decode(json_encode($disposisidrafts), true);
		}

		return view('pages.bpaddisposisi.formdisposisi')
				->with('access', $access)
				->with('disposisisents', $disposisisents)
				->with('disposisidrafts', $disposisidrafts)
				->with('signnow', $signnow)
				->with('searchnow', $request->searchnow)
				->with('monthnow', $monthnow)
				->with('yearnow', $yearnow);
	}

	public function disposisitambah(Request $request)
	{
		$this->checkSessionTime();

		$maxnoform = Fr_disposisi::max('no_form');
		if (is_null($maxnoform)) {
			$maxnoform = '1.20.512.'.substr(date('Y'), -2).'100001';
		} else {
			$splitmaxform = explode(".", $maxnoform);
			$maxnoform = $splitmaxform[0] . '.' . $splitmaxform[1] . '.' . $splitmaxform[2] . '.' . substr(date('Y'), -2) . substr(($splitmaxform[3]+1), -6);
		}
		$kddispos = Glo_disposisi_kode::orderBy('kd_jnssurat')->get();

		$unitkerjas = DB::select( DB::raw("SELECT TOP (1000) [sts]
											  ,[uname]
											  ,[tgl]
											  ,[ip]
											  ,[logbuat]
											  ,[kd_skpd]
											  ,[kd_unit]
											  ,[nm_unit]
											  ,[cp_unit]
											  ,[notes]
											  ,[child]
											  ,[sao]
											  ,[tgl_unit]
										  FROM [bpaddtfake].[dbo].[glo_org_unitkerja]
										  order by kd_unit") );
		$unitkerjas = json_decode(json_encode($unitkerjas), true);

		$stafs = DB::select( DB::raw("
					SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
						CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
						CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
						CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
						CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
						,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
						and tbunit.kd_unit like '01%' and ked_emp = 'aktif' order by nm_emp") );
		$stafs = json_decode(json_encode($stafs), true);

		$jabatans = DB::select( DB::raw("SELECT [sts]
														  ,[uname]
														  ,[tgl]
														  ,[ip]
														  ,[logbuat]
														  ,[kd_skpd]
														  ,[jns_jab]
														  ,[jabatan]
														  ,[disposisi]
													  FROM [bpaddtfake].[dbo].[glo_org_jabatan]
													  where disposisi = 'Y'
													  order by jabatan asc") );
		$jabatans = json_decode(json_encode($jabatans), true);

		$penanganans = Glo_disposisi_penanganan::
						orderBy('urut')
						->get();

		return view('pages.bpaddisposisi.disposisitambah')
				->with('maxnoform', $maxnoform)
				->with('kddispos', $kddispos)
				->with('stafs', $stafs)
				->with('unitkerjas', $unitkerjas)
				->with('jabatans', $jabatans)
				->with('penanganans', $penanganans);
	}

	public function disposisiubah(Request $request)
	{
		// if (file_exists("C:/xampp/htdocs/portal/public/publicfile/disp/1.20.512.20102228/disp19.pdf" )) {
		$dispmaster = DB::select( DB::raw("SELECT TOP (100) [ids]
												  ,[sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[kd_unit]
												  ,[no_form]
												  ,[kd_surat]
												  ,[status_surat]
												  ,[idtop]
												  ,[tgl_masuk]
												  ,[usr_input]
												  ,[tgl_input]
												  ,[no_index]
												  ,[kode_disposisi]
												  ,[perihal]
												  ,[tgl_surat]
												  ,[no_surat]
												  ,[asal_surat]
												  ,[kepada_surat]
												  ,[sifat1_surat]
												  ,[sifat2_surat]
												  ,[ket_lain]
												  ,[nm_file]
												  ,[kepada]
												  ,[noid]
												  ,[penanganan]
												  ,[catatan]
												  ,[from_user]
												  ,[from_pm]
												  ,[to_user]
												  ,[to_pm]
												  ,[rd]
												  ,[usr_rd]
												  ,[tgl_rd]
												  ,[selesai]
												  ,[child]
												  ,[penanganan_final]
												  ,[catatan_final]
												  FROM [bpaddtfake].[dbo].[fr_disposisi]
												  where ids like '$request->ids'
												  and sts = 1
												  order by tgl_masuk desc, no_form desc"))[0];
		$dispmaster = json_decode(json_encode($dispmaster), true);

		$treedisp = '<tr>
						<td>
							<span class="fa fa-book"></span> <span>'.$dispmaster['no_form'].'</span> <br>
							<span class="text-muted">Kode: '.$dispmaster['kode_disposisi'].'</span> | <span class="text-muted"> Nomor: '.$dispmaster['no_surat'].'</span><br>

						</td>
					</tr>';

		$treedisp .= $this->display_disposisi($request->no_form, $dispmaster['ids']);

		$kddispos = Glo_disposisi_kode::orderBy('kd_jnssurat')->get();

		$unitkerjas = DB::select( DB::raw("SELECT TOP (1000) [sts]
											  ,[uname]
											  ,[tgl]
											  ,[ip]
											  ,[logbuat]
											  ,[kd_skpd]
											  ,[kd_unit]
											  ,[nm_unit]
											  ,[cp_unit]
											  ,[notes]
											  ,[child]
											  ,[sao]
											  ,[tgl_unit]
										  FROM [bpaddtfake].[dbo].[glo_org_unitkerja]
										  order by kd_unit") );
		$unitkerjas = json_decode(json_encode($unitkerjas), true);

		// $stafs = DB::select( DB::raw("
		// 			SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
		// 				CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
		// 				CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
		// 				CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
		// 				CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
		// 				,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
		// 				and tbunit.kd_unit like '01%' and ked_emp = 'aktif' order by nm_emp") );
		// $stafs = json_decode(json_encode($stafs), true);

		$jabatans = DB::select( DB::raw("SELECT TOP (1000) [sts]
														  ,[uname]
														  ,[tgl]
														  ,[ip]
														  ,[logbuat]
														  ,[kd_skpd]
														  ,[jns_jab]
														  ,[jabatan]
														  ,[disposisi]
													  FROM [bpaddtfake].[dbo].[glo_org_jabatan]
													  where disposisi = 'Y'
													  order by jabatan asc") );
		$jabatans = json_decode(json_encode($jabatans), true);

		$penanganans = Glo_disposisi_penanganan::
						orderBy('urut')
						->get();

		$treedisp = '<tr>
						<td>
							<span class="fa fa-book"></span> <span>'.$dispmaster['no_form'].'</span> <br>
							<span class="text-muted">Kode: '.$dispmaster['kode_disposisi'].'</span> | <span class="text-muted"> Nomor: '.$dispmaster['no_surat'].'</span><br>

						</td>
					</tr>';

		$treedisp .= $this->display_disposisi($dispmaster['no_form'], $dispmaster['ids']);

		return view('pages.bpaddisposisi.disposisiubah')
				->with('dispmaster', $dispmaster)
				->with('treedisp', $treedisp)
				->with('kepada', $dispmaster['kepada'])
				->with('kddispos', $kddispos)
				->with('treedisp', $treedisp)
				// ->with('stafs', $stafs)
				->with('unitkerjas', $unitkerjas)
				->with('jabatans', $jabatans)
				->with('penanganans', $penanganans);
	}

	public function disposisihapusfile(Request $request)
	{
		unlink(config('app.savefiledisposisi') . "/" . $request->no_form . "/" . $request->nm_file );
		$nmfilebefore = Fr_disposisi::where('ids', $request->ids)->get();
		$nmfilenew = '';
		
		$splitnmfile = explode("::", $nmfilebefore[0]['nm_file']);
		foreach ($splitnmfile as $key => $nm_file) {
			if ($nm_file != $request->nm_file) {
				if ($key != 0 && $nm_file != $request->nm_file) {
					$nmfilenew .= "::";
				}
				$nmfilenew .= $nm_file;
			}
		}

		Fr_disposisi::where('ids', $request->ids)
			->update([
				'nm_file' => $nmfilenew,
			]);

		return 0;
	}

	public function forminsertdisposisi(Request $request)
	{
		$this->checkSessionTime();

		if (isset($request->jabatans) && isset($request->stafs)) {
			return redirect('/disposisi/tambah disposisi')
					->with('message', 'Tidak boleh memilih jabatan & staf bersamaan')
					->with('msg_num', 2);
		}

		if (isset($request->btnDraft)) {
			$status_surat = 'd';
			$selesai = 'Y';
			$child = 0;
		} else {
			if (count($request->jabatans) > 1 || strpos(strtolower($request->jabatans[0]),"kepala badan") === false ) {
				return redirect('/disposisi/tambah disposisi')
						->with('message', 'Hanya boleh memilh Kepala Badan untuk memulai alur disposisi')
						->with('msg_num', 2);
			}
			$status_surat = 's';
			if (isset($request->jabatans)) {
				$selesai = '';
				$child = 1;
			} elseif (isset($request->stafs)) {
				$selesai = '';
				$child = 1;
			} else {
				$selesai = 'Y';
				$child = 0;
			}
		}

		if ($status_surat == 's' && is_null($request->jabatans) && is_null($request->stafs)) {
			return redirect('/disposisi/tambah disposisi')
					->with('message', 'Harus memilih untuk melanjutkan disposisi')
					->with('msg_num', 2);
		}

		$ceknoform = Fr_disposisi::where('no_form', $request->newnoform)->count();
		if ($ceknoform != 0) {
			$maxnoform = Fr_disposisi::max('no_form');
			if (is_null($maxnoform)) {
				$maxnoform = '1.20.512.'.substr(date('Y'), -2).'100001';
			} else {
				$splitmaxform = explode(".", $maxnoform);
				$maxnoform = $splitmaxform[0] . '.' . $splitmaxform[1] . '.' . $splitmaxform[2] . '.' . substr(date('Y'), -2) . substr(($splitmaxform[3] + 1), -6);
			}
		} else {
			$maxnoform = $request->newnoform;
			$splitmaxform = explode(".", $maxnoform);
			$maxnoform = $splitmaxform[0] . '.' . $splitmaxform[1] . '.' . $splitmaxform[2] . '.' . substr(date('Y'), -2) . substr(($splitmaxform[3]), -6);
		}

		$diryear = date('Y');
		if (isset($request->nm_file)) {
			$file = $request->nm_file;
			if (count($file) == 1) {
				$filedispo = 'disp';

				if ($file[0]->getSize() > 52222222) {
					return redirect('/disposisi/tambah disposisi')->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
				} 

				$filedispo .= ($splitmaxform[3]);
				$filedispo .= ".". $file[0]->getClientOriginalExtension();

				$tujuan_upload = config('app.savefiledisposisi');
				$tujuan_upload .= "\\" . $diryear;
				$tujuan_upload .= "\\" . $maxnoform;
				$file[0]->move($tujuan_upload, $filedispo);
			} else {
				$filedispo = '';
				foreach ($file as $key => $data) {
					$filenow = 'disp';

					if ($data->getSize() > 52222222) {
						return redirect('/disposisi/tambah disposisi')->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
					} 

					$filenow .= $key;
					$filenow .= ($splitmaxform[3]);
					$filenow .= ".". $data->getClientOriginalExtension();

					$tujuan_upload = config('app.savefiledisposisi');
					$tujuan_upload .= "\\" . $diryear;
					$tujuan_upload .= "\\" . $maxnoform;
					$data->move($tujuan_upload, $filenow);

					if ($key != count($file) - 1) {
						$filedispo .= $filenow . "::";
					} else {
						$filedispo .= $filenow;
					}
				}
			}	
		} else {
			$filedispo = '';
		}


		// var_dump($request->all());
		// die();

		$kepada = '';
		if (isset($request->jabatans)) {
			for ($i=0; $i < count($request->jabatans); $i++) { 
				$kepada .= $request->jabatans[$i];
				if ($i != (count($request->jabatans) - 1)) {
					$kepada .= "::";
				}
			}
		}

		$insertsuratmaster = [
			'sts' => 1,
			'uname'     => (Auth::user()->usname ? Auth::user()->usname : Auth::user()->id_emp),
			'tgl'       => date('Y-m-d H:i:s'),
			'ip'        => '',
			'logbuat'   => '',
			'kd_skpd'	=> '1.20.512',
			'kd_unit'	=> $request->kd_unit,
			'no_form' => $maxnoform,
			'kd_surat' => $request->kd_surat,
			'status_surat' => $status_surat,
			'idtop' => 0,
			'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
			'usr_input' => (isset(Auth::user()->usname) ? Auth::user()->usname : Auth::user()->id_emp),
			'tgl_input' => date('Y-m-d H:i:s'),
			'no_index' => (isset($request->no_index) ? $request->no_index : '' ),
			'kode_disposisi' => $request->kode_disposisi,
			'perihal' => (isset($request->perihal) ? $request->perihal : '' ),
			'tgl_surat' => (isset($request->tgl_surat) ? date('m/d/Y', strtotime(strtr($request->tgl_surat, '/', '-'))) : null ),
			'no_surat' => (isset($request->no_surat) ? $request->no_surat : '' ),
			'asal_surat' => (isset($request->asal_surat) ? $request->asal_surat : '' ),
			'kepada_surat' => (isset($request->kepada_surat) ? $request->kepada_surat : '' ),
			'sifat1_surat' => (isset($request->sifat1_surat) ? $request->sifat1_surat : '' ),
			'sifat2_surat' => (isset($request->sifat2_surat) ? $request->sifat2_surat : '' ),
			'ket_lain' => (isset($request->ket_lain) ? $request->ket_lain : '' ),
			'nm_file' => $filedispo,
			'kepada' => $kepada,
			'noid' => '',
			'penanganan' => (isset($request->penanganan) ? $request->penanganan : '' ),
			'catatan' => (isset($request->catatan) ? $request->catatan : '' ),
			'from_user' => '',
			'from_pm' => '',
			'to_user' => '',
			'to_pm' => '',
			'rd' => '',
			'usr_rd' => null,
			'tgl_rd' => null,
			'selesai' => $selesai,
			'child' => $child,
		];

		Fr_disposisi::insert($insertsuratmaster);
		$idnew = Fr_disposisi::max('ids');

		if ($request->btnDraft) {
			return redirect('/disposisi/formdisposisi')
					->with('message', 'Disposisi berhasil dibuat')
					->with('msg_num', 1);
		}

		if ($request->btnKirim) {

			if (isset($request->jabatans)) {
				for ($i=0; $i < count($request->jabatans); $i++) { 
					$findidemp = DB::select( DB::raw("
							SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
								CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
								CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
								CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
								CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
								,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
								and tbjab.idjab like '".$request->jabatans[$i]."' and ked_emp = 'aktif'") )[0];
					$findidemp = json_decode(json_encode($findidemp), true);

					$insertsurat = [
						'sts' => 1,
						'uname'     => (Auth::user()->usname ? Auth::user()->usname : Auth::user()->id_emp),
						'tgl'       => date('Y-m-d H:i:s'),
						'ip'        => '',
						'logbuat'   => '',
						'kd_skpd'	=> '1.20.512',
						'kd_unit'	=> $request->kd_unit,
						'no_form' => $maxnoform,
						'kd_surat' => null,
						'status_surat' => null,
						'idtop' => $idnew,
						'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
						'usr_input' => '',
						'tgl_input' => null,
						'no_index' => '',
						'kode_disposisi' => '',
						'perihal' => '',
						'tgl_surat' => null,
						'no_surat' => '',
						'asal_surat' => '',
						'kepada_surat' => '',
						'sifat1_surat' => '',
						'sifat2_surat' => '',
						'ket_lain' => '',
						'nm_file' => '',
						'kepada' => '',
						'noid' => '',
						'penanganan' => '',
						'catatan' => '',
						'from_user' => (Auth::user()->usname ? 'A' : 'E'),
						'from_pm' => (isset(Auth::user()->usname) ? Auth::user()->usname : Auth::user()->id_emp),
						'to_user' => 'E',
						'to_pm' => $findidemp['id_emp'],
						'rd' => 'N',
						'usr_rd' => null,
						'tgl_rd' => null,
						'selesai' => '',
						'child' => 0,
					];
					Fr_disposisi::insert($insertsurat);
				}
					
			}

			// if (isset($request->stafs)) {
			// 	for ($i=0; $i < count($request->stafs); $i++) { 
			// 		$findidemp = DB::select( DB::raw("
			// 				SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
			// 					CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
			// 					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
			// 					CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
			// 					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
			// 					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
			// 					and id_emp like '".$request->stafs[$i]."' and ked_emp = 'aktif'") )[0];
			// 		$findidemp = json_decode(json_encode($findidemp), true);

			// 		$insertsurat = [
			// 			'sts' => 1,
			// 			'uname'     => (Auth::user()->usname ? Auth::user()->usname : Auth::user()->id_emp),
			// 			'tgl'       => date('Y-m-d H:i:s'),
			// 			'ip'        => '',
			// 			'logbuat'   => '',
			// 			'kd_skpd'	=> '1.20.512',
			// 			'kd_unit'	=> $request->kd_unit,
			// 			'no_form' => $maxnoform,
			// 			'kd_surat' => null,
			// 			'status_surat' => null,
			// 			'idtop' => $idnew,
			// 			'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
			// 			'usr_input' => '',
			// 			'tgl_input' => null,
			// 			'no_index' => '',
			// 			'kode_disposisi' => '',
			// 			'perihal' => '',
			// 			'tgl_surat' => null,
			// 			'no_surat' => '',
			// 			'asal_surat' => '',
			// 			'kepada_surat' => '',
			// 			'sifat1_surat' => '',
			// 			'sifat2_surat' => '',
			// 			'ket_lain' => '',
			// 			'nm_file' => '',
			// 			'kepada' => '',
			// 			'noid' => '',
			// 			'penanganan' => '',
			// 			'catatan' => '',
			// 			'from_user' => (Auth::user()->usname ? 'A' : 'E'),
			// 			'from_pm' => (isset(Auth::user()->usname) ? Auth::user()->usname : Auth::user()->id_emp),
			// 			'to_user' => 'E',
			// 			'to_pm' => $findidemp['id_emp'],
			// 			'rd' => 'N',
			// 			'usr_rd' => null,
			// 			'tgl_rd' => null,
			// 			'selesai' => '',
			// 			'child' => 0,
			// 		];
			// 		Fr_disposisi::insert($insertsurat);
			// 	}
			// }
		}

		return redirect('/disposisi/formdisposisi')
					->with('message', 'Disposisi berhasil dibuat')
					->with('msg_num', 1);
	}

	public function formupdatedisposisi(Request $request)
	{
		$this->checkSessionTime();

		if (isset($request->jabatans) && isset($request->stafs)) {
			return redirect('/disposisi/ubah disposisi?ids='.$request->ids)
					->with('message', 'Tidak boleh memilih jabatan & staf bersamaan')
					->with('msg_num', 2);
		}

		if (isset($request->btnDraft)) {
			$status_surat = 'd';
			$selesai = 'Y';
			$child = 0;
		} else {
			if (count($request->jabatans) > 1 || strpos(strtolower($request->jabatans[0]),"kepala badan") === false ) {
				return redirect('/disposisi/ubah disposisi?ids='.$request->ids)
						->with('message', 'Hanya boleh memilh Kepala Badan untuk memulai alur disposisi')
						->with('msg_num', 2);
			}
			$status_surat = 's';
			if (isset($request->jabatans)) {
				$selesai = '';
				$child = 1;
			} elseif (isset($request->stafs)) {
				$selesai = '';
				$child = 1;
			} else {
				$selesai = 'Y';
				$child = 0;
			}
		}

		if ($status_surat == 's' && is_null($request->jabatans) && is_null($request->stafs)) {
			return redirect('/disposisi/ubah disposisi?ids='.$request->ids)
					->with('message', 'Harus memilih untuk melanjutkan disposisi')
					->with('msg_num', 2);
		}

		$splitmaxform = explode(".", $request->no_form);

		$nowdisposisi = Fr_disposisi::where('ids', $request->ids)->first();
		$filedispo = $nowdisposisi['nm_file'];

		$diryear = date('Y',strtotime($request->tgl));
		if (isset($request->nm_file)) {
			$file = $request->nm_file;
			if (count($file) == 1) {
				
				if ($file[0]->getSize() > 52222222) {
					return redirect('/disposisi/ubah disposisi?ids='.$request->ids)->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
				} 

				if ($filedispo != '') {
					$filedispo .= '::';
				}

				$filenow = 'disp';
				$filenow .= (int) date('HIs');
				$filenow .= ($splitmaxform[3]);
				$filenow .= ".". $file[0]->getClientOriginalExtension();

				$tujuan_upload = config('app.savefiledisposisi');
				$tujuan_upload .= "\\" . $diryear;
				$tujuan_upload .= "\\" . $request->no_form;

				$filedispo .= $filenow;

				$file[0]->move($tujuan_upload, $filenow);
			} else {
				if ($filedispo != '') {
					$filedispo .= '::';
				}

				foreach ($file as $key => $data) {

					if ($data->getSize() > 52222222) {
						return redirect('/disposisi/ubah disposisi?ids='.$request->ids)->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
					} 

					$filenow = 'disp';
					$filenow .= (int) date('HIs') + $key;
					$filenow .= ($splitmaxform[3]);
					$filenow .= ".". $data->getClientOriginalExtension();

					$tujuan_upload = config('app.savefiledisposisi');
					$tujuan_upload .= "\\" . $diryear;
					$tujuan_upload .= "\\" . $request->no_form;
					$data->move($tujuan_upload, $filenow);

					// if ($key != count($file) - 1) {
					// 	$filedispo .= $filenow . "::";
					// } else {
					// 	$filedispo .= $filenow;
					// }
				
					if ($key != 0) {
						$filedispo .= "::";
					} 
					$filedispo .= $filenow;

				}
			}
			Fr_disposisi::where('ids', $request->ids)
			->update([
				'nm_file' => $filedispo,
			]);	
		}

		$kepada = '';
		if (isset($request->jabatans)) {
			for ($i=0; $i < count($request->jabatans); $i++) { 
				$kepada .= $request->jabatans[$i];
				if ($i != (count($request->jabatans) - 1)) {
					$kepada .= "::";
				}
			}
		}

		Fr_disposisi::where('ids', $request->ids)
			->update([
			'kd_unit'	=> $request->kd_unit,
			'status_surat' => $status_surat,
			'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
			'no_index' => (isset($request->no_index) ? $request->no_index : '' ),
			'kode_disposisi' => $request->kode_disposisi,
			'perihal' => (isset($request->perihal) ? $request->perihal : '' ),
			'tgl_surat' => (isset($request->tgl_surat) ? date('m/d/Y', strtotime(strtr($request->tgl_surat, '/', '-'))) : null ),
			'no_surat' => (isset($request->no_surat) ? $request->no_surat : '' ),
			'asal_surat' => (isset($request->asal_surat) ? $request->asal_surat : '' ),
			'kepada_surat' => (isset($request->kepada_surat) ? $request->kepada_surat : '' ),
			'sifat1_surat' => (isset($request->sifat1_surat) ? $request->sifat1_surat : '' ),
			'sifat2_surat' => (isset($request->sifat2_surat) ? $request->sifat2_surat : '' ),
			'ket_lain' => (isset($request->ket_lain) ? $request->ket_lain : '' ),
			'kepada' => $kepada,
			'penanganan' => (isset($request->penanganan) ? $request->penanganan : '' ),
			'catatan' => (isset($request->catatan) ? $request->catatan : '' ),
			'selesai' => $selesai,
			'child' => $child,
		]);
		$idnew = $request->ids;

		if ($request->btnDraft) {
			return redirect('/disposisi/formdisposisi')
					->with('message', 'Disposisi berhasil diubah')
					->with('msg_num', 1);
		}

		if ($request->btnKirim) {

			if (isset($request->jabatans)) {
				for ($i=0; $i < count($request->jabatans); $i++) { 
					$findidemp = DB::select( DB::raw("
							SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
								CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
								CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
								CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
								CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
								,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
								and tbjab.idjab like '".$request->jabatans[$i]."' and ked_emp = 'aktif'") )[0];
					$findidemp = json_decode(json_encode($findidemp), true);

					$insertsurat = [
						'sts' => 1,
						'uname'     => (Auth::user()->usname ? Auth::user()->usname : Auth::user()->id_emp),
						'tgl'       => date('Y-m-d H:i:s'),
						'ip'        => '',
						'logbuat'   => '',
						'kd_skpd'	=> '1.20.512',
						'kd_unit'	=> $request->kd_unit,
						'no_form' => $request->no_form,
						'kd_surat' => null,
						'status_surat' => null,
						'idtop' => $idnew,
						'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
						'usr_input' => '',
						'tgl_input' => null,
						'no_index' => '',
						'kode_disposisi' => '',
						'perihal' => '',
						'tgl_surat' => null,
						'no_surat' => '',
						'asal_surat' => '',
						'kepada_surat' => '',
						'sifat1_surat' => '',
						'sifat2_surat' => '',
						'ket_lain' => '',
						'nm_file' => '',
						'kepada' => ($request->jabatans[0] ? $request->jabatans[0] : ''),
						'noid' => '',
						'penanganan' => '',
						'catatan' => '',
						'from_user' => (Auth::user()->usname ? 'A' : 'E'),
						'from_pm' => (isset(Auth::user()->usname) ? Auth::user()->usname : Auth::user()->id_emp),
						'to_user' => 'E',
						'to_pm' => $findidemp['id_emp'],
						'rd' => 'N',
						'usr_rd' => null,
						'tgl_rd' => null,
						'selesai' => '',
						'child' => 0,
					];
					Fr_disposisi::insert($insertsurat);
				}
					
			}

			// if (isset($request->stafs)) {
			// 	for ($i=0; $i < count($request->stafs); $i++) { 
			// 		$findidemp = DB::select( DB::raw("
			// 				SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
			// 					CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
			// 					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
			// 					CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
			// 					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
			// 					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
			// 					and id_emp like '".$request->stafs[$i]."' and ked_emp = 'aktif'") )[0];
			// 		$findidemp = json_decode(json_encode($findidemp), true);

			// 		$insertsurat = [
			// 			'sts' => 1,
			// 			'uname'     => (Auth::user()->usname ? Auth::user()->usname : Auth::user()->id_emp),
			// 			'tgl'       => date('Y-m-d H:i:s'),
			// 			'ip'        => '',
			// 			'logbuat'   => '',
			// 			'kd_skpd'	=> '1.20.512',
			// 			'kd_unit'	=> $request->kd_unit,
			// 			'no_form' => $maxnoform,
			// 			'kd_surat' => null,
			// 			'status_surat' => null,
			// 			'idtop' => $idnew,
			// 			'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
			// 			'usr_input' => '',
			// 			'tgl_input' => null,
			// 			'no_index' => '',
			// 			'kode_disposisi' => '',
			// 			'perihal' => '',
			// 			'tgl_surat' => null,
			// 			'no_surat' => '',
			// 			'asal_surat' => '',
			// 			'kepada_surat' => '',
			// 			'sifat1_surat' => '',
			// 			'sifat2_surat' => '',
			// 			'ket_lain' => '',
			// 			'nm_file' => '',
			// 			'kepada' => ($request->jabatans[0] ? $request->jabatans[0] : ''),
			// 			'noid' => '',
			// 			'penanganan' => '',
			// 			'catatan' => '',
			// 			'from_user' => (Auth::user()->usname ? 'A' : 'E'),
			// 			'from_pm' => (isset(Auth::user()->usname) ? Auth::user()->usname : Auth::user()->id_emp),
			// 			'to_user' => 'E',
			// 			'to_pm' => $findidemp['id_emp'],
			// 			'rd' => 'N',
			// 			'usr_rd' => null,
			// 			'tgl_rd' => null,
			// 			'selesai' => '',
			// 			'child' => 0,
			// 		];
			// 		Fr_disposisi::insert($insertsurat);
			// 	}
			// }
		}

		return redirect('/disposisi/formdisposisi')
					->with('message', 'Disposisi berhasil dikirim')
					->with('msg_num', 1);
	}

	public function formdeletedisposisi(Request $request)
	{
		Fr_disposisi::where('no_form', $request->no_form)
		->update([
			'sts' => 0,
		]);

		return 0;
	}

	// ---------/ADMIN----------- //

	// ---------EMPLOYEE----------- //

	public function disposisi(Request $request)
	{
		$this->checkSessionTime();
		$currentpath = str_replace("%20", " ", $_SERVER['REQUEST_URI']);
		$currentpath = explode("?", $currentpath)[0];
		$thismenu = Sec_menu::where('urlnew', $currentpath)->first('ids');
		$access = $this->checkAccess($_SESSION['user_data']['idgroup'], $thismenu['ids']);

		if ($request->yearnow) {
			$yearnow = (int)$request->yearnow;
		} else {
			$yearnow = (int)date('Y');
		}

		if ($request->monthnow) {
			$monthnow = (int)$request->monthnow;
		} else {
			$monthnow = (int)date('m');
		}

		if ($request->signnow) {
			$signnow = $request->signnow;
		} else {
			$signnow = "=";
		}

		if ($request->searchnow) {
			$qsearchnow = "and (m.kd_surat = '".$request->searchnow."' or m.no_form = '".$request->searchnow."')";
		} else {
			$qsearchnow = "";
		}

		$idgroup = $_SESSION['user_data']['id_emp'];
		if (is_null($idgroup)) {
			$qid = '';
		} else {
			$qid = "and d.to_pm = '".$idgroup."'";
		}

		$dispinbox = DB::select( DB::raw("SELECT TOP (100) d.[ids]
												  ,d.[sts]
												  ,d.[uname]
												  ,d.[tgl]
												  ,m.tgl as tglmaster
												  ,d.[ip]
												  ,d.[logbuat]
												  ,d.[kd_skpd]
												  ,d.[kd_unit]
												  ,d.[no_form]
												  ,d.[kd_surat]
												  ,d.[status_surat]
												  ,d.[idtop]
												  ,t.ids as parent
												  ,d.[tgl_masuk]
												  ,d.[usr_input]
												  ,d.[tgl_input]
												  ,m.[no_index]
												  ,m.[kode_disposisi]
												  ,m.[perihal]
												  ,m.[tgl_surat]
												  ,m.[no_surat]
												  ,m.[asal_surat]
												  ,m.[kepada_surat]
												  ,m.[sifat1_surat]
												  ,m.[sifat2_surat]
												  ,d.[ket_lain]
												  ,m.[nm_file]
												  ,t.[kepada]
												  ,d.[noid]
												  ,t.[penanganan]
												  ,t.[catatan]
												  ,d.[from_user]
												  ,d.[from_pm]
												  ,emp1.nm_emp as from_nm
												  ,d.[to_user]
												  ,d.[to_pm]
												  ,emp2.nm_emp as to_nm
												  ,d.[rd]
												  ,d.[usr_rd]
												  ,d.[tgl_rd]
												  ,d.[selesai]
												  ,d.[child]
												  FROM [bpaddtfake].[dbo].[fr_disposisi] d
												  left join bpaddtfake.dbo.emp_data as emp1 on emp1.id_emp = d.from_pm
												  left join bpaddtfake.dbo.emp_data as emp2 on emp2.id_emp = d.to_pm
												  left join bpaddtfake.dbo.fr_disposisi as t on t.ids = d.idtop
												  left join bpaddtfake.dbo.fr_disposisi as m on m.no_form = d.no_form and m.idtop = 0
												  where (d.rd like 'Y' or d.rd like 'N')
												  and month(d.tgl_masuk) $signnow $monthnow
												  and year(d.tgl_masuk) $signnow $yearnow
												  and d.sts = 1
												  AND d.idtop > 0 AND d.child = 0
												  $qid
												  $qsearchnow
												  order by d.tgl_masuk desc, d.no_form desc, d.ids asc"));
		$dispinbox = json_decode(json_encode($dispinbox), true);

		$dispdraft = DB::select( DB::raw("SELECT TOP (100) d.[ids]
												  ,d.[sts]
												  ,d.[uname]
												  ,d.[tgl]
												  ,m.tgl as tglmaster
												  ,d.[ip]
												  ,d.[logbuat]
												  ,d.[kd_skpd]
												  ,d.[kd_unit]
												  ,d.[no_form]
												  ,d.[kd_surat]
												  ,d.[status_surat]
												  ,d.[idtop]
												  ,d.[tgl_masuk]
												  ,d.[usr_input]
												  ,d.[tgl_input]
												  ,m.[no_index]
												  ,m.[kode_disposisi]
												  ,m.[perihal]
												  ,m.[tgl_surat]
												  ,m.[no_surat]
												  ,m.[asal_surat]
												  ,m.[kepada_surat]
												  ,m.[sifat1_surat]
												  ,m.[sifat2_surat]
												  ,d.[ket_lain]
												  ,m.[nm_file]
												  ,d.[kepada]
												  ,d.[noid]
												  ,d.[penanganan]
												  ,d.[catatan]
												  ,d.[from_user]
												  ,d.[from_pm]
												  ,emp1.nm_emp as from_nm
												  ,d.[to_user]
												  ,d.[to_pm]
												  ,emp2.nm_emp as to_nm
												  ,d.[rd]
												  ,d.[usr_rd]
												  ,d.[tgl_rd]
												  ,d.[selesai]
												  ,d.[child]
												  FROM [bpaddtfake].[dbo].[fr_disposisi] d
												  left join bpaddtfake.dbo.emp_data as emp1 on emp1.id_emp = d.from_pm
												  left join bpaddtfake.dbo.emp_data as emp2 on emp2.id_emp = d.to_pm
												  left join bpaddtfake.dbo.fr_disposisi as m on m.no_form = d.no_form and m.idtop = 0
												  where d.rd like 'D'
												  and month(d.tgl_masuk) $signnow $monthnow
												  and year(d.tgl_masuk) $signnow $yearnow
												  and d.sts = 1
												  AND d.idtop > 0 AND d.child = 0
												  $qid
												  $qsearchnow
												  order by d.tgl_masuk desc, d.no_form desc"));
		$dispdraft = json_decode(json_encode($dispdraft), true);

		if (strlen($_SESSION['user_data']['idunit']) == 8) {
			$qid = "and d.from_pm = '".$idgroup."'";
			$rd = "(d.rd like 'N' or d.rd like 'Y')";
		} else {
			$rd = "d.rd like 'S'";
		}

		$dispsent = DB::select( DB::raw("SELECT TOP (100) d.[ids]
												  ,d.[sts]
												  ,d.[uname]
												  ,d.[tgl]
												  ,m.tgl as tglmaster
												  ,d.[ip]
												  ,d.[logbuat]
												  ,d.[kd_skpd]
												  ,d.[kd_unit]
												  ,d.[no_form]
												  ,d.[kd_surat]
												  ,d.[status_surat]
												  ,d.[idtop]
												  ,d.[tgl_masuk]
												  ,d.[usr_input]
												  ,d.[tgl_input]
												  ,m.[no_index]
												  ,m.[kode_disposisi]
												  ,m.[perihal]
												  ,m.[tgl_surat]
												  ,m.[no_surat]
												  ,m.[asal_surat]
												  ,m.[kepada_surat]
												  ,m.[sifat1_surat]
												  ,m.[sifat2_surat]
												  ,d.[ket_lain]
												  ,m.[nm_file]
												  ,d.[kepada]
												  ,d.[noid]
												  ,d.[penanganan]
												  ,d.[catatan]
												  ,d.[from_user]
												  ,d.[from_pm]
												  ,emp1.nm_emp as from_nm
												  ,d.[to_user]
												  ,d.[to_pm]
												  ,emp2.nm_emp as to_nm
												  ,d.[rd]
												  ,d.[usr_rd]
												  ,d.[tgl_rd]
												  ,d.[selesai]
												  ,d.[child]
												  FROM [bpaddtfake].[dbo].[fr_disposisi] d
												  left join bpaddtfake.dbo.emp_data as emp1 on emp1.id_emp = d.from_pm
												  left join bpaddtfake.dbo.emp_data as emp2 on emp2.id_emp = d.to_pm
												  left join bpaddtfake.dbo.fr_disposisi as m on m.no_form = d.no_form and m.idtop = 0
												  where $rd
												  and month(d.tgl_masuk) $signnow $monthnow
												  and year(d.tgl_masuk) $signnow $yearnow
												  and d.sts = 1
												  $qid
												  $qsearchnow
												  order by d.tgl_masuk desc, d.no_form desc, d.ids asc"));
		$dispsent = json_decode(json_encode($dispsent), true);

		// var_dump($dispsent);
		// die();


		return view('pages.bpaddisposisi.disposisi')
				->with('access', $access)
				->with('dispinbox', $dispinbox)
				->with('dispsent', $dispsent)
				->with('dispdraft', $dispdraft)
				->with('signnow', $signnow)
				->with('searchnow', $request->searchnow)
				->with('monthnow', $monthnow)
				->with('yearnow', $yearnow);
	}

	public function disposisilihat(Request $request)
	{
		$dispmaster = DB::select( DB::raw("SELECT d.[ids]
												  ,m.ids as idmaster
												  ,d.[sts]
												  ,d.[uname]
												  ,d.[tgl]
												  ,m.tgl as tglmaster
												  ,d.[ip]
												  ,d.[logbuat]
												  ,d.[kd_skpd]
												  ,d.[kd_unit]
												  ,unit.nm_unit
												  ,d.[no_form]
												  ,m.[kd_surat]
												  ,d.[status_surat]
												  ,d.[idtop]
												  ,d.[tgl_masuk]
												  ,d.[usr_input]
												  ,d.[tgl_input]
												  ,m.[no_index]
												  ,m.[kode_disposisi]
												  ,kode.nm_jnssurat
												  ,m.[perihal]
												  ,m.[tgl_surat]
												  ,m.[no_surat]
												  ,m.[asal_surat]
												  ,m.[kepada_surat]
												  ,m.[sifat1_surat]
												  ,m.[sifat2_surat]
												  ,d.[ket_lain]
												  ,m.[nm_file]
												  ,d.[kepada]
												  ,d.[noid]
												  ,d.[penanganan]
												  ,d.[catatan]
												  ,d.[from_user]
												  ,d.[from_pm]
												  ,emp1.nm_emp as from_nm
												  ,d.[to_user]
												  ,d.[to_pm]
												  ,emp2.nm_emp as to_nm
												  ,d.[rd]
												  ,d.[usr_rd]
												  ,d.[tgl_rd]
												  ,d.[selesai]
												  ,d.[child]
												  FROM [bpaddtfake].[dbo].[fr_disposisi] d
												  left join bpaddtfake.dbo.emp_data as emp1 on emp1.id_emp = d.from_pm
												  join bpaddtfake.dbo.emp_data as emp2 on emp2.id_emp = d.to_pm
												  join bpaddtfake.dbo.fr_disposisi as m on m.no_form = d.no_form and m.idtop = 0
												  join bpaddtfake.dbo.glo_org_unitkerja as unit on unit.kd_unit = d.kd_unit
												  join bpaddtfake.dbo.Glo_disposisi_kode as kode on kode.kd_jnssurat = m.kode_disposisi 
												  and d.ids = '$request->ids'"))[0];
		$dispmaster = json_decode(json_encode($dispmaster), true); 


		$to_pm = $dispmaster['to_pm'];
		$tujuan = DB::select( DB::raw("SELECT id_emp, tbjab.idunit FROM bpaddtfake.dbo.emp_data as a
			CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
			CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
			,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
			and id_emp like '$to_pm'"))[0];
		$tujuan = json_decode(json_encode($tujuan), true);

		if (strtolower($dispmaster['rd']) == 'n') {
			Fr_disposisi::where('ids', $dispmaster['ids'])
			->update([
				'rd' => 'Y',
				'usr_rd' => ($_SESSION['user_data']['id_emp'] ? $_SESSION['user_data']['id_emp'] : $_SESSION['user_data']['usname']),
				'tgl_rd' => date('Y-m-d'),
			]);
		} else {
			Fr_disposisi::where('ids', $dispmaster['ids'])
			->update([
				'usr_rd' => ($_SESSION['user_data']['id_emp'] ? $_SESSION['user_data']['id_emp'] : $_SESSION['user_data']['usname']),
				'tgl_rd' => date('Y-m-d'),
			]);
		}

		$treedisp = '<tr>
						<td>
							<span class="fa fa-book"></span> <span>'.$dispmaster['no_form'].'</span> <br>
							<span class="text-muted">Kode: '.$dispmaster['kode_disposisi'].'</span> | <span class="text-muted"> Nomor: '.$dispmaster['no_surat'].'</span><br>

						</td>
					</tr>';

		$treedisp .= $this->display_disposisi($dispmaster['no_form'], $dispmaster['idmaster']);

		if (isset($_SESSION['user_data']['id_emp'])) {
			$kd_unit = $_SESSION['user_data']['idunit'];
		} else {
			$kd_unit = "01";
		}

		$stafs = DB::select( DB::raw("
					SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
						CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
						CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
						CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
						CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
						,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
						and tbunit.sao like '$kd_unit%' and ked_emp = 'aktif' order by nm_emp") );
		$stafs = json_decode(json_encode($stafs), true);

		if (strlen($_SESSION['user_data']['idunit']) == 10 && !(isset($_SESSION['user_data']['usname']))) {
			$jabatans = 0;
			$stafs = 0;
		} else {
			$jabatans = DB::select( DB::raw("SELECT [sts]
												  ,[uname]
												  ,[tgl]
												  ,[ip]
												  ,[logbuat]
												  ,[kd_skpd]
												  ,[jns_jab]
												  ,[jabatan]
												  ,[disposisi]
											  FROM [bpaddtfake].[dbo].[glo_org_jabatan]
											  where disposisi = 'Y'
											  order by jabatan asc") );
			$jabatans = json_decode(json_encode($jabatans), true);
		}

		$penanganans = Glo_disposisi_penanganan::
						orderBy('urut')
						->get();
		

		return view('pages.bpaddisposisi.disposisilihat')
				->with('dispmaster', $dispmaster)
				->with('treedisp', $treedisp)
				->with('stafs', $stafs)
				->with('tujuan', $tujuan)
				->with('penanganans', $penanganans)
				->with('jabatans', $jabatans);
	}

	public function formlihatdisposisi(Request $request)
	{
		$this->checkSessionTime();

		if (isset($request->jabatans) && isset($request->stafs)) {
			return redirect('/disposisi/lihat disposisi?ids='.$request->ids)
					->with('message', 'Tidak boleh memilih jabatan & staf bersamaan')
					->with('msg_num', 2);
		}

		// var_dump($request->all());
		// die();

		if (isset($request->btnDraft)) {
			$rd = 'D';
		} else {
			$rd = 'S';
			if (is_null($request->jabatans) && is_null($request->stafs)) {
				$selesai = 'Y';
				$child = 0;
			} else {
				$selesai = '';
				$child = 1;
			}
		}

		$splitmaxform = explode(".", $request->no_form);

		$filedispo = $request->nm_file_master;

		$diryear = date('Y',strtotime($request->tglmaster));
		if (isset($request->nm_file)) {
			$file = $request->nm_file;
			if (count($file) == 1) {
				
				if ($file[0]->getSize() > 52222222) {
					return redirect('/disposisi/lihat disposisi?ids='.$request->ids)->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
				} 

				if ($filedispo != '') {
					$filedispo .= '::';
				}

				$filenow = 'disp';
				$filenow .= (int) date('HIs');
				$filenow .= ($splitmaxform[3]);
				$filenow .= ".". $file[0]->getClientOriginalExtension();

				$tujuan_upload = config('app.savefiledisposisi');
				$tujuan_upload .= "\\" . $diryear;
				$tujuan_upload .= "\\" . $request->no_form;

				$filedispo .= $filenow;

				$file[0]->move($tujuan_upload, $filenow);
			} else {
				if ($filedispo != '') {
					$filedispo .= '::';
				}

				foreach ($file as $key => $data) {

					if ($data->getSize() > 52222222) {
						return redirect('/disposisi/lihat disposisi?ids='.$request->ids)->with('message', 'Ukuran file terlalu besar (Maksimal 2MB)');     
					} 

					$filenow = 'disp';
					$filenow .= (int) date('HIs') + $key;
					$filenow .= ($splitmaxform[3]);
					$filenow .= ".". $data->getClientOriginalExtension();

					$tujuan_upload = config('app.savefiledisposisi');
					$tujuan_upload .= "\\" . $diryear;
					$tujuan_upload .= "\\" . $request->no_form;
					$data->move($tujuan_upload, $filenow);

					// if ($key != count($file) - 1) {
					// 	$filedispo .= $filenow . "::";
					// } else {
					// 	$filedispo .= $filenow;
					// }
				
					if ($key != 0) {
						$filedispo .= "::";
					} 
					$filedispo .= $filenow;

				}
			}
			Fr_disposisi::where('ids', $request->idmaster)
			->update([
				'nm_file' => $filedispo,
			]);	
		}

		$kepada = '';
		if (isset($request->jabatans)) {
			for ($i=0; $i < count($request->jabatans); $i++) { 
				$kepada .= $request->jabatans[$i];
				if ($i != (count($request->jabatans) - 1)) {
					$kepada .= "::";
				}
			}
		}

		$noid = '';
		if (isset($request->stafs)) {
			if (count($request->stafs) == 1) {
				$noid = $request->stafs[0];
			}
		}

		if (isset($request->btnDraft)) {
			Fr_disposisi::where('ids', $request->ids)
				->update([
				'usr_input' => (Auth::user()->id_emp ? Auth::user()->id_emp : $request->from_pm_new),
				'tgl_input' => date('Y-m-d'),
				'kepada' => $kepada,
				'penanganan' => (isset($request->penanganan) ? $request->penanganan : '' ),
				'catatan' => (isset($request->catatan) ? $request->catatan : '' ),
				'rd' => 'D',
			]);

			return redirect('/disposisi/disposisi')
					->with('message', 'Disposisi berhasil diubah')
					->with('msg_num', 1);
		}

		if (isset($request->btnKirim)) {
			Fr_disposisi::where('ids', $request->ids)
				->update([
				'usr_input' => (Auth::user()->id_emp ? Auth::user()->id_emp : $request->from_pm_new),
				'tgl_input' => date('Y-m-d'),
				'kepada' => $kepada,
				'noid' => $noid,
				'penanganan' => (isset($request->penanganan) ? $request->penanganan : '' ),
				'catatan' => (isset($request->catatan) ? $request->catatan : '' ),
				'rd' => 'S',
				'selesai' => $selesai,
				'child' => $child,
			]);

			if (isset($request->jabatans)) {
				for ($i=0; $i < count($request->jabatans); $i++) { 
					$findidemp = DB::select( DB::raw("
							SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
								CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
								CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
								CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
								CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
								,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
								and tbjab.idjab like '".$request->jabatans[$i]."' and ked_emp = 'aktif'") );
					$findidemp = json_decode(json_encode($findidemp), true);

					if (isset($findidemp[0])) {
						$insertsurat = [
							'sts' => 1,
							'uname'     => (Auth::user()->id_emp ? Auth::user()->id_emp : Auth::user()->usname),
							'tgl'       => date('Y-m-d H:i:s'),
							'ip'        => '',
							'logbuat'   => '',
							'kd_skpd'	=> '1.20.512',
							'kd_unit'	=> $request->kd_unit,
							'no_form' => $request->no_form,
							'kd_surat' => null,
							'status_surat' => null,
							'idtop' => $request->ids,
							'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
							'usr_input' => '',
							'tgl_input' => null,
							'no_index' => '',
							'kode_disposisi' => '',
							'perihal' => '',
							'tgl_surat' => null,
							'no_surat' => '',
							'asal_surat' => '',
							'kepada_surat' => '',
							'sifat1_surat' => '',
							'sifat2_surat' => '',
							'ket_lain' => '',
							'nm_file' => '',
							'kepada' => '',
							'noid' => '',
							'penanganan' => '',
							'catatan' => '',
							'from_user' => 'E',
							'from_pm' => (Auth::user()->id_emp ? Auth::user()->id_emp : $request->from_pm_new),
							'to_user' => 'E',
							'to_pm' => $findidemp[0]['id_emp'],
							'rd' => 'N',
							'usr_rd' => null,
							'tgl_rd' => null,
							'selesai' => '',
							'child' => 0,
						];
						Fr_disposisi::insert($insertsurat);
					}
				}
			}

			if (isset($request->stafs)) {
				for ($i=0; $i < count($request->stafs); $i++) { 
					$findidemp = DB::select( DB::raw("
							SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok FROM bpaddtfake.dbo.emp_data as a
								CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
								CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
								CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
								CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja WHERE glo_org_unitkerja.kd_unit = tbjab.idunit) tbunit
								,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
								and id_emp like '".$request->stafs[$i]."' and ked_emp = 'aktif'") );
					$findidemp = json_decode(json_encode($findidemp), true);

					if (isset($findidemp[0])) {
						$insertsurat = [
							'sts' => 1,
							'uname'     => (Auth::user()->id_emp ? Auth::user()->id_emp : Auth::user()->usname),
							'tgl'       => date('Y-m-d H:i:s'),
							'ip'        => '',
							'logbuat'   => '',
							'kd_skpd'	=> '1.20.512',
							'kd_unit'	=> $request->kd_unit,
							'no_form' => $request->no_form,
							'kd_surat' => null,
							'status_surat' => null,
							'idtop' => $request->ids,
							'tgl_masuk' => (isset($request->tgl_masuk) ? date('Y-m-d',strtotime(str_replace('/', '-', $request->tgl_masuk))) : date('Y-m-d')),
							'usr_input' => '',
							'tgl_input' => null,
							'no_index' => '',
							'kode_disposisi' => '',
							'perihal' => '',
							'tgl_surat' => null,
							'no_surat' => '',
							'asal_surat' => '',
							'kepada_surat' => '',
							'sifat1_surat' => '',
							'sifat2_surat' => '',
							'ket_lain' => '',
							'nm_file' => '',
							'kepada' => '',
							'noid' => '',
							'penanganan' => '',
							'catatan' => '',
							'from_user' => 'E',
							'from_pm' => (Auth::user()->id_emp ? Auth::user()->id_emp : $request->from_pm_new),
							'to_user' => 'E',
							'to_pm' => $findidemp[0]['id_emp'],
							'rd' => 'N',
							'usr_rd' => null,
							'tgl_rd' => null,
							'selesai' => '',
							'child' => 0,
						];
						Fr_disposisi::insert($insertsurat);
					}
				}
			}

			return redirect('/disposisi/disposisi')
					->with('message', 'Disposisi berhasil')
					->with('msg_num', 1);
		}
	}

	// ---------/EMPLOYEE----------- //
}