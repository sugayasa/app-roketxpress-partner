<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Collect Payment <span> / List of payment collect by partner</span></h3>
		</div>
	</div>
	<div class="col-12 col-lg-auto mb-10">
		<div class="page-date-range">
            <a class="button button-info button-sm pull-right d-none" id="excelDataCollectPayment" target="_blank" href=""><span><i class="fa fa-file-excel-o"></i>Excel Collect Payment</span></a>
			<button class="button button-primary button-sm pull-right" id="btnAddCollectPayment" data-toggle="modal" data-target="#modal-selectReservationCollectPayment"><span><i class="fa fa-plus"></i>New Collect Payment</span></button>
		</div>
	</div>
</div>
<div class="box mb-10">
	<div class="box-body pb-5">
		<div class="row">
			<div class="col-lg-2 col-sm-6 mb-10">
				<div class="form-group">
					<label for="startDate" class="control-label">Date Collect</label>
					<input type="text" class="form-control input-date-single mb-10 text-center" id="startDate" name="startDate" value="<?=date('01-m-Y')?>">
				</div>
			</div>
			<div class="col-lg-2 col-sm-6 mb-10">
				<div class="form-group">
					<label for="endDate" class="control-label">.</label>
					<input type="text" class="form-control input-date-single text-center" id="endDate" name="endDate" value="<?=date('t-m-Y')?>">
				</div>
			</div>
			<div class="col-lg-2 col-sm-6 mb-10">
				<div class="form-group">
					<label for="optionCollectStatus" class="control-label">Collect Status</label>
					<select id="optionCollectStatus" name="optionCollectStatus" class="form-control">
						<option value="">All Collect Status</option>
						<option value="<?=hashidEncode(0)?>">Uncollected</option>
						<option value="<?=hashidEncode(1)?>">Collected</option>
					</select>
				</div>
			</div>
			<div class="col-lg-2 col-sm-6 mb-10">
				<div class="form-group">
					<label for="optionSettlementStatus" class="control-label">Settlement Status</label>
					<select id="optionSettlementStatus" name="optionSettlementStatus" class="form-control">
						<option value="">All Settlement Status</option>
						<option value="<?=hashidEncode(0)?>">Unrequested</option>
						<option value="<?=hashidEncode(1)?>">Requested</option>
						<option value="<?=hashidEncode(2)?>">Approved</option>
						<option value="<?=hashidEncode(3)?>">Rejected</option>
					</select>
				</div>
			</div>
			<div class="col-lg-4 col-sm-12 mb-10">
				<div class="form-group">
					<label class="control-label">Search by Customer Name/Booking Code/Remark</label>
                    <input type="text" class="form-control mb-10" id="searchKeyword" name="searchKeyword" placeholder="Type something and push ENTER to search">
				</div>
			</div>
			<div class="col-lg-12 mb-10">
				<div class="form-group">
					<label class="adomx-checkbox">
						<input type="checkbox" id="checkboxViewActiveCollectOnly" name="checkboxViewActiveCollectOnly" value="1"> <i class="icon"></i> <b>Show active collect payment only</b>
					</label>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-12">
				<div class="row">
					<div class="col-lg-8 col-sm-12 mb-10">
						<span id="tableDataCountCollectPayment"></span>
					</div>
                    <div class="col-lg-4 col-sm-12 mb-10">
                        <div class="row">
                            <div class="col-3 text-right align-self-center px-1">Order By</div>
                            <div class="col-6 px-1">
                                <select id="optionOrderBy" name="optionOrderBy" class="form-control">
                                    <option value="<?=hashidEncode(1)?>">Collect Date</option>
                                    <option value="<?=hashidEncode(2)?>">Customer Name</option>
                                </select>
                            </div>
                            <div class="col-3 px-1">
                                <select id="optionOrderType" name="optionOrderType" class="form-control">
                                    <option value="ASC">Asc</option>
                                    <option value="DESC">Desc</option>
                                </select>
                            </div>
                        </div>
                    </div>
				</div>
				<div class="row mt-5 responsive-table-container">
					<table class="table" id="table-collectPayment">
						<thead class="thead-light">
							<tr>
								<th width="150" align="center">Date</th>
								<th >Details</th>
								<th width="200">Remark & Description</th>
								<th width="150" class="text-right">Amount</th>
								<th width="150">Status</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th colspan="5" class="text-center">No data found</th>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="row mt-20">
					<div class="col-sm-12 mb-10">
						<ul class="pagination" id="tablePaginationCollectPayment"></ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-selectReservationCollectPayment" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<form class="modal-content form-horizontal" id="container-selectReservationCollectPayment">
			<div class="modal-header">
				<h4 class="modal-title" id="editor-title-selectReservationCollectPayment">Search Reservation for Collect Payment</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row mb-5">
					<div class="form-group col-lg-2 col-sm-6">
						<label for="reservationDateStart" class="control-label">Reservation Date</label>
						<input type="text" class="form-control input-date-single mb-10 text-center" id="reservationDateStart" name="reservationDateStart" value="<?=date('01-m-Y')?>">
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<label for="reservationDateEnd" class="control-label">.</label>
						<input type="text" class="form-control input-date-single mb-10 text-center" id="reservationDateEnd" name="reservationDateEnd" value="<?=date('d-m-Y')?>">
					</div>
					<div class="form-group col-lg-8 col-sm-12">
						<label for="reservationKeyword" class="control-label">Search reservation by booking code / reservation title / customer name</label>
						<input type="text" class="form-control" id="reservationKeyword" name="reservationKeyword" placeholder="Type something and press ENTER to search" maxlength="150">
					</div>
				</div>
				<div style="height: 400px;overflow-y: scroll;" class="row mb-5 border mx-1 my-2 rounded" id="containerSelectReservationResult">
					<div class="col-sm-12 text-center mx-auto my-auto">
						<h2><i class="fa fa-list-alt text-warning"></i></h2>
						<b class="text-warning">Results goes here</b>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button button-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-editorNewCollectPayment">
	<div class="modal-dialog modal-lg" role="document">
		<form class="modal-content form-horizontal" id="editor-newCollectPayment">
			<div class="modal-header">
				<h4 class="modal-title" id="editor-title-editorNewCollectPayment">Add New Collect Payment</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12 mb-15 pb-15" style="border-bottom: 1px solid #e0e0e0;">
						<h6 class="mb-0">Reservation Detail</h6>
						<div class="order-details-customer-info">
							<ul class="ml-5">
								<li> <span>Source</span> <span id="newCollectPayment-sourceName">-</span> </li>
								<li> <span>Booking Code</span> <span id="newCollectPayment-bookingCode">-</span> </li>
								<li> <span>Reservation Title</span> <span id="newCollectPayment-reservationTitle">-</span> </li>
								<li> <span>Reservation Date</span> <span id="newCollectPayment-reservationDate">-</span> </li>
								<li> <span>Cust. Name</span> <span id="newCollectPayment-customerName">-</span> </li>
								<li> <span>Pax</span> <span id="newCollectPayment-paxDetail">-</span> </li>
							</ul>
						</div>
					</div>
					<div class="col-sm-12 mb-10">
						<div class="form-group required">
							<label for="descriptionPayment" class="control-label">Payment Description</label>
							<input type="text" class="form-control" id="descriptionPayment" name="descriptionPayment" placeholder="Description" maxlength="150">
						</div>
					</div>
					<div class="col-lg-5 col-sm-12 mb-10">
						<div class="form-group required">
							<label for="paymentCurrency" class="control-label">Currency</label>
							<select class="form-control" id="paymentCurrency" name="paymentCurrency">
								<option value="IDR">IDR</option>
								<option value="USD">USD</option>
							</select>
						</div>
					</div>
					<div class="col-lg-4 col-sm-6 mb-10">
						<div class="form-group required">
							<label for="paymentAmountInteger" class="control-label">Integer</label>
							<input type="text" class="form-control mb-10 text-right" id="paymentAmountInteger" name="paymentAmountInteger" onkeypress="maskNumberInput(0, 999999999, 'paymentAmountInteger');" value="0">
						</div>
					</div>
					<div class="col-lg-3 col-sm-6 mb-10">
						<div class="form-group required">
							<label for="paymentAmountDecimal" class="control-label">Comma</label>
							<input type="text" class="form-control mb-10 text-right" id="paymentAmountDecimal" name="paymentAmountDecimal" onkeypress="maskNumberInput(0, 99, 'paymentAmountDecimal');" value="0">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" value="" name="newCollectPayment-idReservation" id="newCollectPayment-idReservation"/>
				<input type="hidden" value="" name="newCollectPayment-scheduleDate" id="newCollectPayment-scheduleDate"/>
				<button type="button" class="button button-info" data-dismiss="modal">Cancel</button>
				<button type="submit" class="button button-primary">Save</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-detailCollectPayment" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content form-horizontal" id="container-detailCollectPayment">
			<div class="modal-header">
				<h4 class="modal-title" id="modal-title-detailCollectPayment">Detail Collect Payment</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-5 col-sm-12">
						<h6 class="mb-0">Customer Detail</h6>
						<div class="order-details-customer-info">
							<ul class="ml-5">
								<li> <span>Source</span> <span id="sourceStr">-</span> </li>
								<li> <span>Booking Code</span> <span id="bookingCodeStr">-</span> </li>
								<li> <span>Reservation Title</span> <span id="reservationTitleStr">-</span> </li>
								<li> <span>Reservation Date</span> <span id="reservationDateStr">-</span> </li>
								<li> <span>Cust. Name</span> <span id="customerNameStr">-</span> </li>
								<li> <span>Contact</span> <span id="cuctomerContactStr">-</span> </li>
								<li> <span>Email</span> <span id="customerEmailStr">-</span> </li>
							</ul>
						</div>
					</div>
					<div class="col-lg-4 col-sm-12">
						<div class="row">
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Date Collect</h6>
								<p id="dateCollectStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Collect Payment Amount</h6>
								<p id="collectPaymentAmountStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Reservation Remark</h6>
								<p id="reservationRemarkStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Payment Remark</h6>
								<p id="paymentRemarkStr">-</p>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-sm-12">
						<div class="row">
							<div class="col-sm-12">
								<h6 class="mb-0">Settlement Receipt</h6>
								<img src="<?=$defaultImage?>" id="settlementReceipt" style="max-height:300px; max-width:250px"/><br/>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-12 mt-5"><h6>History</h6></div>
					<div class="col-12">
						<table class="table" id="table-collectPaymentHistory">
							<thead class="thead-light">
								<tr>
									<th width="140" class="text-center">Date Time</th>
									<th>Description</th>
									<th width="160">User Input</th>
									<th width="40"></th>
								</tr>
							</thead>
							<tbody>
								<tr id="noDataCollectPaymentHistory">
									<td colspan="4" class="text-center text-bold">No history found</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button button-primary button-sm" id="btnRequestSettlement">Request Settlement</button>
				<button type="button" class="button button-default button-sm" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-settlementCollectPayment" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<form class="modal-content form-horizontal" id="content-settlementCollectPayment">
			<div class="modal-header">
				<h4 class="modal-title" id="modal-title-settlementCollectPayment">Request Settlement Collect Payment</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-7 col-sm-12">
						<div class="row">
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Cust. Name</h6>
								<p id="settlementCollect-customerNameStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Date Collect</h6>
								<p id="settlementCollect-dateCollectStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Collect Payment Amount</h6>
								<p id="settlementCollect-collectPaymentAmountStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Reservation Remark</h6>
								<p id="settlementCollect-reservationRemarkStr">-</p>
							</div>
							<div class="col-sm-12 mb-10">
								<h6 class="mb-0">Payment Remark</h6>
								<p id="settlementCollect-paymentRemarkStr">-</p>
							</div>
						</div>
					</div>
					<div class="col-lg-5 col-sm-12">
                        <h6 class="mb-0">Please Upload Your Transfer Receipt</h6>
                        <img src="<?=$defaultImage?>" id="settlementCollect-settlementReceipt" style="max-height:300px; max-width:250px"/><br/>
                        <div id="uploaderSettlementReceipt" class="mt-20">Upload Transfer Receipt</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="settlementCollect-settlementReceiptFileName" name="settlementCollect-settlementReceiptFileName" value="">
				<input type="hidden" id="settlementCollect-idCollectPayment" name="settlementCollect-idCollectPayment" value="">
				<button class="button button-primary button-sm">Submit Settlement</button>
				<button type="button" class="button button-default button-sm" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-zoomReceiptSettlement">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content form-horizontal" style="background-color: transparent; border: none;">
			<div class="modal-header" style="border:none">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12 mx-auto text-center">
						<img src="" width="600px" id="zoomImageReceiptSettlement">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-confirmCollectPayment" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<form class="modal-content form-horizontal" id="content-confirmCollectPayment">
			<div class="modal-header">
				<h5 class="modal-title" id="title-confirmCollectPayment">Confirm Collect Payment</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12">
                        <h6 class="mb-0">Collect Payment Details</h6>
                        <div id="confirmCollectPayment-detailCollectPayment"></div>
                    </div>
					<div class="col-sm-12 mt-10">
                        <h6>Please Re-enter Nominal</h6>
                        <div class="form-group my-0">
                            <input type="text" class="form-control text-right" id="confirmCollectPayment-reenterNominal" name="confirmCollectPayment-reenterNominal" maxlength="10" onkeypress="maskNumberInput(1, 99999999, 'confirmCollectPayment-reenterNominal')">
                        </div>
					</div>
					<div class="col-sm-12 mt-10">
                        <h6>Remark / Notes</h6>
                        <div class="form-group my-0">
                            <input type="text" class="form-control" id="confirmCollectPayment-remarkNotes" name="confirmCollectPayment-remarkNotes">
                        </div>
					</div>
    			</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="confirmCollectPayment-idReservation" name="confirmCollectPayment-idReservation" value="">
				<input type="hidden" id="confirmCollectPayment-idSchedule" name="confirmCollectPayment-idSchedule" value="">
				<input type="hidden" id="confirmCollectPayment-nominal" name="confirmCollectPayment-nominal" value="">
				<input type="hidden" id="confirmCollectPayment-collectDate" name="confirmCollectPayment-collectDate" value="">
				<button class="button button-primary" id="btnSubmitConfirmCollectPayment">Confirm This Collect Payment</button>
				<button type="button" class="button button-default" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<script>
	var url = "<?=BASE_URL_ASSETS_JS?>page-module/collectPayment.js?<?=date('YmdHis')?>",
    defaultImageReceipt = '<?=$defaultImage?>';
	$.getScript(url);
</script>