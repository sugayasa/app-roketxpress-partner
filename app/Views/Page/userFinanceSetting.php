<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">User & Finance Setting <span> / Setting bank data, secret pin and user list</span></h3>
		</div>
	</div>
</div>
<div class="rowPartner rounded-lg row px-3 py-3 mx-0 mb-20 bg-white">
    <div class="col-lg-3 col-sm-12 text-center">
        <div class="author-profile">
            <div class="image">
                <h1 id="partnerDetail-initial">-</h1>
            </div>
        </div>
    </div>
    <div class="col-lg-9 col-sm-12 mt-10" id="containerDetailPartner">
        <p class="mb-0 pr-10"><b id="partnerDetail-name">-</b></p>
        <p class="mb-0 pr-10" id="partnerDetail-address">-</p>
        <p class="mb-0 pr-10" id="partnerDetail-phoneEmail">-</p><br>
    </div>
    <div class="col-sm-12 mt-20" id="containerPINInfoWarning"></div>
    <div class="col-sm-12 mt-20">
        <h6 id="containerActiveBankAccount">Current active bank account</h6>
        <img src="<?=URL_BANK_LOGO?>default.png" style="max-height:30px; max-width:90px" class="mb-10" id="partnerDetail-bankLogo"><br/>
        <ul class="list-icon"><li class="pl-0"><i class="fa fa-university mr-3"></i><span id="partnerDetail-bankName">-</span></li></ul>
        <ul class="list-icon"><li class="pl-0"><i class="fa fa-credit-card mr-3"></i><span id="partnerDetail-bankAccountNumber">-</span></li></ul>
        <ul class="list-icon"><li class="pl-0"><i class="fa fa-user-circle mr-3"></i><span id="partnerDetail-bankAccountHolder">-</span></li></ul>
    </div>
</div>
<ul class="nav nav-tabs mb-20" id="tabsPanelDetail">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#listUserPartnerTab"><i class="fa fa-money"></i> List of Users</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#listBankAccountTab"><i class="fa fa-list-alt"></i> List of Inactive Bank Accounts</a></li>
</ul>
<div class="box">
    <div class="box-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="listUserPartnerTab">
                <div class="row">
                    <div class="col-lg-8 col-sm-12 mb-5 d-flex">
                        <span id="tableDataCountUserPartner" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                        <button class="button button-primary button-sm pull-right" id="btnAddUserPartner" data-toggle="modal" data-target="#modal-editorUserPartner"><span><i class="fa fa-plus"></i>New User</span></button>
                    </div>
                    <div class="col-sm-12 tableFixHead" style="height:300px">
                        <table class="table" id="table-listUserPartner">
                            <thead class="thead-light">
                                <tr>
                                    <th width="240">Name</th>
                                    <th width="240">User Name</th>
                                    <th width="240">Email</th>
                                    <th width="240">Level</th>
                                    <th width="180">Status</th>
                                    <th>Last Login</th>
                                    <th width="160"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="listBankAccountTab">
                <div class="row">
                    <div class="col-lg-8 col-sm-12 mb-5 d-flex">
                        <span id="tableDataCountBankAccount" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                        <button class="button button-primary button-sm pull-right" id="btnAddBankAccount" data-toggle="modal" data-target="#modal-editorBankAccount"><span><i class="fa fa-plus"></i>New Bank Account</span></button>
                    </div>
                    <div class="col-sm-12 tableFixHead" style="height:300px">
                        <table class="table" id="table-listBankAccount">
                            <thead class="thead-light">
                                <tr>
                                    <th width="280">Bank Name</th>
                                    <th width="160">Account Number</th>
                                    <th>Bank Account Holder</th>
                                    <th width="160"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="modal-createSecretPIN" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-sm" role="document">
		<form class="modal-content form-horizontal" id="editor-createSecretPIN">
			<div class="modal-header">
				<h5 class="modal-title" id="title-confirmSchedule">Create Secret PIN</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12 px-1"><h6>Type your 4 digit PIN</h6></div>
					<div class="col-sm-12">
        				<div class="row">
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInput" id="pinInput1" name="pinInput1" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInput" id="pinInput2" name="pinInput2" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInput" id="pinInput3" name="pinInput3" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInput" id="pinInput4" name="pinInput4" value="" maxlength="1">
                            </div>
                        </div>
                    </div>
					<div class="col-sm-12 px-1">
                        <p>* Please always remember your secret PIN</p>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="button button-primary" id="btnCreateSecretPIN">Create PIN</button>
				<button type="button" class="button button-default" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-editorBankAccount">
	<div class="modal-dialog modal-md" role="document">
		<form class="modal-content form-horizontal" id="form-editorBankAccount">
			<div class="modal-header">
				<h4 class="modal-title" id="editor-title-editorBankAccount">Input Bank Account</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-6 col-sm-12 mb-10">
						<div class="form-group required">
							<label for="optionAccountBank" class="control-label">Bank</label>
							<select class="form-control" id="optionAccountBank" name="optionAccountBank"></select>
						</div>
					</div>
					<div class="col-lg-6 col-sm-12 mb-10">
						<div class="form-group required">
							<label for="accountNumber" class="control-label">Account Number</label>
							<input type="text" class="form-control" id="accountNumber" name="accountNumber" placeholder="Account Number" maxlength="20">
						</div>
					</div>
					<div class="col-sm-12 mb-10 border-bottom">
						<div class="form-group required">
							<label for="accountHolderName" class="control-label">Account Holder Name</label>
							<input type="text" class="form-control" id="accountHolderName" name="accountHolderName" placeholder="Account Holder Name" maxlength="50">
						</div>
					</div>
					<div class="col-sm-12 mb-10">
						<div class="form-group required">
							<label class="control-label text-center">Your Secret PIN</label>
						</div>
					</div>
					<div class="col-auto">
        				<div class="row px-5">
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputBankAccount" id="pinInputBankAccount1" name="pinInputBankAccount1" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputBankAccount" id="pinInputBankAccount2" name="pinInputBankAccount2" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputBankAccount" id="pinInputBankAccount3" name="pinInputBankAccount3" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputBankAccount" id="pinInputBankAccount4" name="pinInputBankAccount4" value="" maxlength="1">
                            </div>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" value="" name="idBankAccountPartner" id="idBankAccountPartner"/>
				<button type="button" class="button button-info" data-dismiss="modal">Cancel</button>
				<button type="submit" class="button button-primary">Save</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-inputOTPBankAccount" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-sm" role="document">
		<form class="modal-content form-horizontal" id="editor-inputOTPBankAccount">
			<div class="modal-header">
				<h5 class="modal-title" id="title-confirmSchedule">Input Your Verification Code</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12 px-1"><h6 id="messageOTPInput"></h6></div>
					<div class="col-sm-12">
        				<div class="row">
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInput" id="otpInput1" name="otpInput1" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInput" id="otpInput2" name="otpInput2" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInput" id="otpInput3" name="otpInput3" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInput" id="otpInput4" name="otpInput4" value="" maxlength="1">
                            </div>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="button button-primary" id="btnSubmitOTPBankAccount">Submit OTP</button>
				<button type="button" class="button button-default" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-editorUserPartner">
	<div class="modal-dialog modal-lg" role="document">
		<form class="modal-content form-horizontal" id="form-editorUserPartner">
			<div class="modal-header">
				<h4 class="modal-title" id="editor-title-editorUserPartner">Add / Edit User</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-7 col-sm-12">
        				<div class="row">
                            <div class="col-lg-6 col-sm-12 mb-10">
                                <div class="form-group required">
                                    <label for="nameUser" class="control-label">Name</label>
                                    <input type="text" class="form-control" id="nameUser" autocomplete="new-password" name="nameUser" placeholder="Name">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 mb-10">
                                <div class="form-group required">
                                    <label for="username" class="control-label">Username</label>
                                    <input type="text" class="form-control" id="usernameEditor" autocomplete="new-password" name="usernameEditor" placeholder="Username">
                                </div>
                            </div>
                            <div class="col-sm-7 mb-10">
                                <div class="form-group required">
                                    <label for="userEmail" class="control-label">Email</label>
                                    <input type="text" class="form-control" id="userEmail" name="userEmail" autocomplete="new-password" placeholder="Email">
                                </div>
                            </div>
                            <div class="col-sm-5 mb-10">
                                <div class="form-group required">
                                    <label for="optionUserLevel" class="control-label">User Level</label>
                                    <select id="optionUserLevel" name="optionUserLevel" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="col-lg-5 col-sm-12">
                        <h6>List of sections accessible by the selected level</h6>
                        <ul class="ml-5" id="listSectionLevel"></ul>
                    </div>
					<div class="col-sm-12 pt-10 mb-20 border-top">
                        <p class="font-weight-bold">Please fill in the password form if you want to change the password</p>
                    </div>
					<div class="col-lg-6 col-sm-12 mb-10">
                        <div class="form-group">
                            <label for="newUserPassword" class="control-label">New Password</label>
                            <input type="password" class="form-control" id="newUserPassword" autocomplete="new-password" name="newUserPassword" placeholder="Password" autocomplete = "new-password">
                        </div>
                    </div>
					<div class="col-lg-6 col-sm-12 mb-10">
                        <div class="form-group">
                            <label for="repeatUserPassword" class="control-label">Repeat Password</label>
                            <input type="password" class="form-control" id="repeatUserPassword" autocomplete="new-password" name="repeatUserPassword" placeholder="Repeat Password" autocomplete = "new-password">
                        </div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" value="" name="idUserPartner" id="idUserPartner"/>
				<button type="button" class="button button-info" data-dismiss="modal">Cancel</button>
				<button type="submit" class="button button-primary">Save</button>
			</div>
		</form>
	</div>
</div>
<script>
	var urlImageBankLogo=   '<?=URL_BANK_LOGO?>',
        url 		    =	"<?=BASE_URL_ASSETS_JS?>page-module/userFinanceSetting.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>
<style>
.rowPartner{
	border: 1px solid #e0e0e0;
	min-height: 120px;
	overflow: hidden;
}
.author-profile .image {
  width: 160px;
  height: 160px;
  overflow: hidden;
  position: relative;
  border-radius: 50%;
  margin: auto;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
      -ms-flex-pack: center;
          justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
      -ms-flex-align: center;
          align-items: center;
  background-color: #f1f1f1;
}
.author-profile .image h1 {
  font-size: 50px;
  margin: 0;
  font-weight: 700;
}
</style>