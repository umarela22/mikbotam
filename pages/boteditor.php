<?php
$bottype = isset($_GET['bottype']) ? $_GET['bottype'] : 'Core';
$save_error = '';

if (isset($_POST['save'])) {
	if (!defined('MIKBOTAM_ALLOW_BOT_EDITOR') || MIKBOTAM_ALLOW_BOT_EDITOR !== true) {
		$save_error = 'Pengeditan file source via Web UI dinonaktifkan demi alasan keamanan (RCE Protection). Untuk mengaktifkan, definisikan MIKBOTAM_ALLOW_BOT_EDITOR true pada system.config.php.';
	} else {
		$file = '../Saldo/Core.php';
		if ($bottype == "Nonsaldo") {
			$file = '../Saldo/Core_Nonsaldo.php';
		} elseif ($bottype == "saldononsaldo") {
			$file = '../Saldo/Core_Saldo_Nonsaldo.php';
		} elseif ($bottype == "default") {
			$file = '../Saldo/Core_default.php';
		}

		$openthis = @fopen($file, 'w');
		if ($openthis) {
			$data = $_POST['editor'];
			fwrite($openthis, $data);
			fclose($openthis);
			$save_success = 'File berhasil diperbarui.';
		} else {
			$save_error = 'Gagal membuka file: ' . htmlspecialchars($file);
		}
	}
}

?>

<!-- Load Lib ace editor -->   
<script src="../lib/ace/ace/ace.js"></script>
<script src="../lib/ace/ace/theme-twilight.js"></script>
<script src="../lib/ace/ace/mode-php.js"></script>
<script src="../lib/ace/jquery-ace.min.js"></script>


<div class="sl-pagebody pd-sm-5">
	<?php if (!empty($save_error)): ?>
		<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<strong>Peringatan Keamanan!</strong> <?= htmlspecialchars($save_error) ?>
		</div>
	<?php endif; ?>
	<?php if (!empty($save_success)): ?>
		<div class="alert alert-success" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<?= htmlspecialchars($save_success) ?>
		</div>
	<?php endif; ?>
	 <div class="row row-sm">
<div class="col-sm-8">
       <div class="card bd-primary mg-t-3">
                    <div class="card-header bg-primary tx-white"><i class="icol-ui-tab-content"></i>   Edit Bot Core.php</div>
                    <div class="card-body pd-sm-5 ">
				<form autocomplete="off" method="post" action="">
					
							<div class="pd-10">
						
								<button type="submit" title="Save <?=$bottype?>.php" class="btn bg-primary tx-white" name="save"><i class="fa fa-save"></i> Save</button>
							   <button type="submit" title="Default" class="btn bg-info tx-white" name="default"><i class="fa fa-save"></i> Default</button>
							
							
											<select class="form-control select2id" onchange="window.location.href=this.value "  style="width: 94px;" data-placeholder="Select Type">
											
									   <option><?= ucfirst($bottype); ?></option>
	    								<option value="./index.php?Mikbotam=boteditor&bottype=Core">Core</option>
	    								<option value="./index.php?Mikbotam=boteditor&bottype=Nonsaldo">Nonsaldo</option>
	    								<option value="./index.php?Mikbotam=boteditor&bottype=saldononsaldo">Saldo  + Nonsaldo</option>
	    								<option value="./index.php?Mikbotam=boteditor&bottype=default">Default</option>

											</select>
										
									
							</div>
	    				
	        	<textarea id="mikbotamedit" class="from-control db" rows="30" style="width: 100%" name="editor">

                        <?php
                        	if ($bottype == "Core") {
                        		echo file_get_contents('../Saldo/Core.php');
                        	} elseif ($bottype == "Nonsaldo") {
                        		echo file_get_contents('../Saldo/Core_Nonsaldo.php');
                        	} elseif ($bottype == "saldononsaldo") {
                        		echo file_get_contents('../Saldo/Core_Saldo_Nonsaldo.php');
                        	} elseif ($bottype == "default") {
                        		echo file_get_contents('../Saldo/Core_default.php');
                        	}
                        ?>

					
					
	        </textarea>
			</form>
			</div>
		</div>
		</div>
		<div class="col-sm-4 mg-t-2">
			 <div class="card bd-primary">
                    <div class="card-header bg-primary tx-white"><i class="fa   fa-info-circle"></i> ReadMe</div>
                    <div class="card-body pd-sm-15">
                    	                        <table>
                            <tbody>
                                <tr>
                                    <td >
                                        <p >
                                            Format <code>Editing</code>.<br>
                                            Folder Mikbotam wajib chmod 0777 
                                            <br>Perhatikan jika anda mengubah File bot pastikan anda hanya mengubah textnya saja jika terjadi kesalahan Editing dapat mengakibatkan bot berhenti atau macet
                                        </p>
                                        
                                					

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    		</div>
		</div>
		</div>
		</div>
	</div>


<script>

 var _0x627b=["\x74\x6F\x6D\x6F\x72\x72\x6F\x77","\x70\x68\x70","\x61\x63\x65","\x23\x6D\x69\x6B\x62\x6F\x74\x61\x6D\x65\x64\x69\x74"];$(_0x627b[3])[_0x627b[2]]({theme:_0x627b[0],lang:_0x627b[1]})
</script>


