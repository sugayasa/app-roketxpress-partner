<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Finance <span> / Finance details, fees and collect payments</span></h3>
		</div>
	</div>
	<div class="col-12 col-lg-auto mb-10">
		<div class="page-date-range">
            <?php if($showBtnWithdraw) { ?>
			<button class="button button-primary button-sm pull-right" id="btnAddWithdraw" data-toggle="modal" data-target="#modal-addWithdrawal"><span><i class="fa fa-plus"></i>Request Withdraw</span></button>
            <?php } ?>
			<button class="button button-warning button-sm pull-right d-none" type="button" id="btnCloseDetails"><span><i class="fa fa-arrow-circle-left"></i>Back</span></button>
		</div>
	</div>
</div>
<div class="slideTransition slideContainer slideLeft show" id="slideContainerLeft">
    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-lg-10 col-sm-6">
                            <label for="maxDateFinance" class="control-label font-weight-bold pt-10 text-right">Max. Date Finance</label>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <input type="text" class="form-control input-date-single mb-10 text-right pull-right" id="maxDateFinance" name="maxDateFinance" value="<?=date('d-m-Y')?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="rowPartner rounded-lg row px-3 py-3 mx-0 mb-1 bg-white">
                        <div class="col-lg-2 col-sm-4 text-center">
                            <div class="author-profile">
                                <div class="image">
                                    <h1 id="partnerDetail-initial">-</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-10 col-sm-8">
                            <div class="row px-0 py-0">
                                <div class="col-lg-8 col-sm-12" id="containerDetailPartner">
                                    <p class="mb-0"><b id="partnerDetail-name">-</b></p>
                                    <p class="mb-0" id="partnerDetail-address">-</p>
                                    <p class="mb-0" id="partnerDetail-phoneEmail">-</p><br>
                                </div>
                                <div class="col-lg-4 col-sm-12" id="withdrawBankAccountDetails">
                                    <img src="<?=URL_BANK_LOGO?>default.png" style="max-height:30px; max-width:90px" class="mb-10" id="partnerDetail-bankLogo"><br/>
                                    <ul class="list-icon"><li class="pl-0"><i class="fa fa-university mr-1"></i><span id="partnerDetail-bankName">-</span></li></ul>
                                    <ul class="list-icon"><li class="pl-0"><i class="fa fa-credit-card mr-1"></i><span id="partnerDetail-bankAccountNumber">-</span></li></ul>
                                    <ul class="list-icon"><li class="pl-0"><i class="fa fa-user-circle mr-1"></i><span id="partnerDetail-bankAccountHolder">-</span></li></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="rowPartner rounded-lg row px-2 py-2 mx-0 mb-1 mt-10 bg-white">
                        <div class="col-xlg-3 col-sm-12 px-0 border-right">
                            <div class="top-report bg-white">
                                <div class="head">
                                    <h4>Total Fee</h4>
                                    <span class="view"><i class="fa fa-money"></i></span>
                                </div>
                                <div class="content">
                                    <h2 id="totalNominalFee">0</h2>
                                </div>
                                <div class="footer">
                                    <div class="progess">
                                        <div class="progess-bar" style="width: 100%;"></div>
                                    </div>
                                    <p>Total Schedule : <span id="totalSchedule">0</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xlg-3 col-sm-12 px-0 border-right">
                            <div class="top-report bg-white">
                                <div class="head">
                                    <h4>Collect Payment</h4>
                                    <span class="view"><i class="fa fa-list-alt"></i></span>
                                </div>
                                <div class="content">
                                    <h2 id="totalNominalCollectPayment">0</h2>
                                </div>
                                <div class="footer">
                                    <div class="progess">
                                        <div class="progess-bar" style="width: 100%;"></div>
                                    </div>
                                    <p>Total Schedule With Collect : <span id="totalCollectPayment">0</span></p>
                                </div>

                            </div>
                        </div>
                        <div class="col-xlg-3 col-sm-12 px-0 border-right">
                            <div class="top-report bg-white">
                                <div class="head">
                                    <h4>Withdraw Balance</h4>
                                    <span class="view"><i class="fa fa-cc-mastercard"></i></span>
                                </div>
                                <div class="content">
                                    <h2 id="totalWithdrawBalance">0</h2>
                                </div>
                                <div class="footer">
                                    <div class="progess">
                                        <div class="progess-bar" style="width: 100%;"></div>
                                    </div>
                                    <p>Last Withdrawal : <span id="lastWitdrawalDate">-</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xlg-3 col-sm-12 px-0">
                            <div class="top-report bg-white">
                                <div class="head">
                                    <h4>Deposit Balance</h4>
                                    <span class="view"><i class="fa fa-cc-amex"></i></span>
                                </div>
                                <div class="content">
                                    <h2 id="totalDepositBalance">0</h2>
                                </div>
                                <div class="footer">
                                    <div class="progess">
                                        <div class="progess-bar" style="width: 100%;"></div>
                                    </div>
                                    <p>Last Transaction : <span id="lastDepositTransactionDate">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 mt-20">
                    <ul class="nav nav-tabs" id="tabsPanelDetail">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#listFeeTab"><i class="fa fa-money"></i> Fee</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#listCollectPaymentTab"><i class="fa fa-list-alt"></i> Collect Payment</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#listDepositTransactionTab"><i class="fa fa-history"></i> Deposit Transaction</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#listWithdrawalTab"><i class="fa fa-credit-card"></i> Withdrawal</a></li>
                    </ul>
                    <div class="tab-content mt-30">
                        <div class="tab-pane fade show active" id="listFeeTab">
                            <div class="row">
                                <div class="col-sm-12 mb-5 d-flex">
                                    <span id="tableDataCountFee" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                                </div>
                                <div class="col-sm-12 tableFixHead">
                                    <table class="table" id="table-listFee">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="120">Date</th>
                                                <th width="250">Source & Booking Code</th>
                                                <th width="250">Customer Name</th>
                                                <th>Reservation Title | Schedule Title</th>
                                                <th class="text-right" width="120">Total Fee</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="listCollectPaymentTab">
                            <div class="row">
                                <div class="col-sm-12 mb-5 d-flex">
                                    <span id="tableDataCountCollectPayment" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                                </div>
                                <div class="col-sm-12 tableFixHead">
                                    <table class="table" id="table-listCollectPayment">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="120">Date</th>
                                                <th>Reservation Details</th>
                                                <th>Remarks</th>
                                                <th>Payment Description</th>
                                                <th class="text-right" width="120">Amount</th>
                                                <th class="text-right" width="120">Amount (IDR)</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="listDepositTransactionTab">
                            <div class="row">
                                <div class="col-12 mb-5 d-flex">
                                    <span id="tableDataCountListDepositTransaction" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                                    <div class="page-date-range pull-right ml-auto">
                                        <input type="text" class="form-control input-date-single mb-10 mr-2 text-center" id="startDateDepositTransaction" name="startDateDepositTransaction" value="<?=date('01-m-Y')?>">
                                        <input type="text" class="form-control input-date-single mb-10 text-center" id="endDateDepositTransaction" name="endDateDepositTransaction" value="<?=date('t-m-Y')?>">
                                    </div>
                                </div>
                                <div class="col-12 tableFixHead">
                                    <table class="table" id="table-listDepositTransaction">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="140">Input Details</th>
                                                <th>Description</th>
                                                <th>Reservation Details</th>
                                                <th>Collect Payment Details</th>
                                                <th class="text-right" width="120">Amount</th>
                                                <th width="60"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="listWithdrawalTab">
                            <div class="row">
                                <div class="col-12 mb-5 d-flex">
                                    <span id="tableDataCountListWithdrawal" class="align-self-center">Show data from 0 to 0. Total 0 data</span>
                                    <div class="page-date-range pull-right ml-auto">
                                        <input type="text" class="form-control input-date-single mb-10 mr-2 text-center" id="startDateWithdrawal" name="startDateWithdrawal" value="<?=date('01-m-Y')?>">
                                        <input type="text" class="form-control input-date-single mb-10 text-center" id="endDateWithdrawal" name="endDateWithdrawal" value="<?=date('t-m-Y')?>">
                                    </div>
                                </div>
                                <div class="col-12 tableFixHead">
                                    <table class="table" id="table-listWithdrawal">
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="slideTransition slideContainer slideRight hide" id="slideContainerRight">
    <div class="box d-none">
		<div class="box-body">
			<div class="row" style="border-bottom: 1px solid #e0e0e0;">
				<div class="col-sm-12" style="border-bottom: 1px solid #e0e0e0;">Date Time Request : <b id="requestDateTimeStr">-</b></div>
				<div class="col-lg-3 col-sm-12 mt-20">
					<div class="row">
						<div class="col-sm-12 mb-10">
							<h6 class="mb-0">Status</h6>
							<p id="badgeStatusWithdrawal">-</p>
						</div>
						<div class="col-sm-12 mb-10">
							<h6 class="mb-0">Message</h6>
							<p id="messageStr">-</p>
						</div>
						<div class="col-sm-12 mb-10">
							<h6 class="mb-3">Bank Account Detail</h6>
							<img style="max-height:30px; max-width:90px" class="mb-10" id="imgBankLogo"><br>
							<ul class="list-icon"><li class="pl-0"><i class="fa fa-university mr-1"></i><span id="bankNameStr"></span></li></ul>
							<ul class="list-icon"><li class="pl-0"><i class="fa fa-credit-card mr-1"></i><span id="accountNumberStr"></span></li></ul>
							<ul class="list-icon"><li class="pl-0"><i class="fa fa-user-circle mr-1"></i><span id="accountHolderNameStr"></span></li></ul>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-sm-12 mt-20">
					<div class="row">
						<div class="col-sm-12 mb-10">
							<div class="order-details-customer-info pb-1" style="border-bottom: 1px solid #dee2e6;">
								<ul>
									<li> <span>Fee</span> <span><b id="totalFeeStr"></b></span> </li>
									<?php if($isDriver){ ?> <li> <span>Additional Cost</span> <span><b id="totalAdditioalCostStr"></b></span> </li><?php } ?>
									<li> <span>Collect Payment</span> <span><b id="totalCollectPaymentStr"></b></span> </li>
									<?php if($isDriver){ ?><li> <span>Prepaid Capital</span> <span><b id="totalPrepaidCapitalStr"></b></span> </li><?php } ?>
									<?php if($isDriver){ ?><li> <span>Loan - Car</span> <span><b id="totalCarInstallmentStr"></b></span> </li><?php } ?>
									<?php if($isDriver){ ?><li> <span>Loan - Personal</span> <span><b id="totalPersonalInstallmentStr"></b></span> </li><?php } ?>
								</ul>
							</div>
							<div class="order-details-customer-info pt-1">
								<ul>
									<li> <span><b>Total Withdrawal</b></span> <span><b id="totalWithdrawalStr"></b></span> </li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-5 col-sm-12 mt-20">
					<div class="row">
						<div class="col-sm-12 mb-10">
							<h6 class="mb-0">Transfer Receipt</h6>
						</div>
						<div class="col-sm-12 mb-10 bg-white" id="transferReceiptPreview"></div>
					</div>
				</div>
				<div class="col-lg-8 col-sm-6 pt-10 mb-10" style="border-top: 1px solid #e0e0e0;">
					<div class="row">
						<div class="col-sm-6">
							<h6 class="mb-0">Approval User</h6>
							<p id="approvalUserStr">-</p>
						</div>
						<div class="col-sm-6">
							<h6 class="mb-0">Date Time Approval</h6>
							<p id="dateTimeApprovalStr">-</p>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 mt-20"><h5>List Of Withdrawal Details</h5></div>
				<div class="col-sm-12 mt-10 tableFixHead" style="max-height: 400px">
					<table class="table" id="table-dataListWithdrawalDetail">
						<thead class="thead-light">
							<tr>
								<th width="150">Type</th>
								<th width="100">Date</th>
								<th>Description</th>
								<th width="120" class="text-right">Amount</th>
							</tr>
						</thead>
						<tbody><td colspan="4" class="text-center">No data found</td></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-zoomReceiptTransfer">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content form-horizontal" style="background-color: transparent; border: none;">
			<div class="modal-header" style="border:none">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12 mx-auto text-center">
						<img src="" width="600px" id="zoomImageReceiptTransfer">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-addWithdrawal" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content form-horizontal" id="container-addWithdrawal">
			<div class="modal-header">
				<h4 class="modal-title" id="modal-title-addWithdrawal">Request Withdrawal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-5 col-sm-12">
						<h6 class="mb-0">Number of Jobs and Collect Payment</h6>
						<div class="order-details-customer-info">
							<ul class="ml-5">
								<li> <span>Jobs</span> <span id="addWithdrawal-numberOfJobs" class="text-right font-weight-bold" style="width:50%">-</span> </li>
								<li> <span>Collect Payment</span> <span id="addWithdrawal-numberOfCollectPayments" class="text-right font-weight-bold" style="width:50%">-</span> </li>
							</ul>
						</div>
                        <h6 class="mt-10 mb-0">Nominal Details</h6>
						<div class="order-details-customer-info" id="withdrawNominalDetails">
							<ul class="ml-5">
								<li> <span>Fee</span> <span id="addWithdrawal-nominalFees" class="text-right font-weight-bold" style="width:50%">-</span> </li>
								<li> <span>Collect Payment</span> <span id="addWithdrawal-nominalCollectPayments" class="text-right font-weight-bold" style="width:50%">-</span> </li>
								<li> <span>Withdraw Balance</span> <span id="addWithdrawal-nominalWithdrawBalance" class="text-right font-weight-bold" style="width:50%">-</span> </li>
							</ul>
						</div>
					</div>
					<div class="col-lg-7 col-sm-12">
                        <div class="form-group">
							<label for="addWithdrawal-notes" class="control-label font-weight-bold">Note / Message (Optional)</label>
							<textarea class="form-control mb-10" id="addWithdrawal-notes" name="addWithdrawal-notes" style="height: 142px;"></textarea>
						</div>
					</div>
                    <div class="col-sm-12 mt-20" id="containerWithdrawalInfoWarning">
                        <div class="alert alert-danger pr-20" role="alert">
                            <i class="fa fa-exclamation-triangle" style="font-size: 18px;"></i> <span id='withdrawalInfoWarning'></span>
                        </div>
                    </div>
				</div>
				<div class="row mt-20">
					<div class="col-12"><h6>List of Details Withdrawal</h6></div>
					<div class="col-12 tableFixHead">
						<table class="table" id="table-listDetailsWithdraw">
							<thead class="thead-light">
								<tr>
									<th width="140" class="text-center">Date</th>
									<th width="140">Type</th>
									<th>Description</th>
									<th width="160">Nominal</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer" id="modal-addWithdrawalFooter">
				<button type="button" class="button button-default button-sm" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-confirmAddWithdrawal" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<form class="modal-content form-horizontal" id="form-confirmAddWithdrawal">
			<div class="modal-header">
				<h4 class="modal-title" id="modal-title-confirmAddWithdrawal">Confirm Withdrawal Request</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-6 col-sm-12 mb-20">
                        <h6 class="mt-10 mb-10">Nominal Details</h6>
						<div class="order-details-customer-info" id="confirmAddWithdrawal-nominalDetails"></div>
					</div>
					<div class="col-lg-6 col-sm-12 mb-20">
                        <h6 class="mt-10 mb-10">Bank Account</h6>
						<div id="confirmAddWithdrawal-bankAccountDetails"></div>
					</div>
					<div class="col-sm-12 pb-10 mb-10 border-bottom">
                        <b>* Please check withdrawal details again. Make sure the nominal fee and collect payment details are correct. Also make sure your bank account data is correct. Enter your secret PIN to continue the process</b>
					</div>
					<div class="col-auto">
        				<div class="row px-5 mx-auto" style="width: 80%;">
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputWithdrawal" id="pinInputWithdrawal1" name="pinInputWithdrawal1" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputWithdrawal" id="pinInputWithdrawal2" name="pinInputWithdrawal2" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputWithdrawal" id="pinInputWithdrawal3" name="pinInputWithdrawal3" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-4">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold pinInputWithdrawal" id="pinInputWithdrawal4" name="pinInputWithdrawal4" value="" maxlength="1">
                            </div>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="button button-primary button-sm" id="btnRequestWithdrawal" data-toggle="modal" data-target="#modal-confirmAddWithdrawal">Submit Request</button>
				<button type="button" class="button button-default button-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-inputOTPWithdrawal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-sm" role="document">
		<form class="modal-content form-horizontal" id="editor-inputOTPWithdrawal">
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
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInputWithdrawal" id="otpInputWithdrawal1" name="otpInputWithdrawal1" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInputWithdrawal" id="otpInputWithdrawal2" name="otpInputWithdrawal2" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInputWithdrawal" id="otpInputWithdrawal3" name="otpInputWithdrawal3" value="" maxlength="1">
                            </div>
                    		<div class="col-sm-3 px-1">
                                <input type="text" class="form-control mb-10 text-center font-weight-bold otpInputWithdrawal" id="otpInputWithdrawal4" name="otpInputWithdrawal4" value="" maxlength="1">
                            </div>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="button button-primary" id="btnSubmitOTPWithdrawal">Submit OTP</button>
				<button type="button" class="button button-default" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<script>
	var urlImageBankLogo=   '<?=URL_BANK_LOGO?>',
        url 		    =	"<?=BASE_URL_ASSETS_JS?>page-module/finance.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>
<style>
.rowPartner{
	border: 1px solid #e0e0e0;
	min-height: 120px;
	max-height: 250px;
	overflow: hidden;
}
.author-profile .image {
  width: 130px;
  height: 130px;
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
@media (max-width: 767px) {
	.withdrawalTableElement{
		overflow: scroll !important;
	}
}
.withdrawalTableElement{
	border: 1px solid #e0e0e0;
	min-height: 120px;
	background-color: #fff;
}
</style>
