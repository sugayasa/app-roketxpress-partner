<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Schedule <span> / List schedule per day for 7 days period</span></h3>
		</div>
	</div>
	<div class="col-12 col-lg-auto">
		<div class="page-date-range">
            <div class="form-group">
                <label for="scheduleDateStart" class="control-label">First Date</label>
    			<input type="text" class="form-control input-date-single text-center py-1" id="scheduleDateStart" name="scheduleDateStart">
	    	</div>
		</div>
	</div>
</div>
<div class="row mt-5 tableFixHead" style="height: 700px">
    <table class="table tableFix" id="table-reservationSchedule">
        <thead class="thead-light">
            <tr id="headerDates">
                <th class="text-center">Time Activity</th>
                <?php
                    foreach($arrDates as $keyDate){
                        $classText  =   in_array($keyDate[2], [1,7]) ? "danger" : "info";
                ?>
                    <th width="210" class="text-center"><b class="text-<?=$classText?>"><?=$keyDate[1]?><br/><?=$keyDate[0]?></b></th>
                <?php
                    }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($arrHour as $keyHour){
            ?>
                <tr>
                    <td align="center"><b><?=$keyHour->VALUE?></b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
</div>
<div class="modal fade" tabindex="-1" id="modal-reservationScheduleDetails" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-xl" role="document">
		<form class="modal-content form-horizontal" id="content-reservationScheduleDetails">
			<div class="modal-header">
				<h5 class="modal-title" id="title-reservationScheduleDetails">Schedule Details</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-lg-6 col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;">
                        <h6 class="mb-0">Booking Detail</h6>
                        <div class="order-details-customer-info pl-2 mb-10">
                            <ul>
                                <li> <span>Source</span> <span id="reservationScheduleDetails-sourceName"></span> </li>
                                <li> <span>Booking Code</span> <span id="reservationScheduleDetails-bookingCode"></span> </li>
                                <li> <span>Reservation Title</span> <span id="reservationScheduleDetails-reservationTitle"></span> </li>
                            </ul>
                        </div>
                        <h6 class="mb-0">Customer Detail</h6>
                        <div class="order-details-customer-info pl-2 mb-10">
                            <ul>
                                <li> <span>Customer Name</span> <span id="reservationScheduleDetails-customerName"></span> </li>
                                <li> <span>Contact</span> <span id="reservationScheduleDetails-customerContact"></span> </li>
                                <li> <span>Email</span> <span id="reservationScheduleDetails-customerEmail"></span> </li>
                            </ul>
                        </div>
                        <h6 class="mb-0">Product & Pax</h6>
                        <div class="order-details-customer-info pl-2">
                            <ul>
                                <li> <span>Product</span> <span id="reservationScheduleDetails-productName"></span> </li>
                                <li> <span>Adult</span> <span id="reservationScheduleDetails-paxAdult"></span> </li>
                                <li> <span>Child</span> <span id="reservationScheduleDetails-paxChild"></span> </li>
                                <li> <span>Infant</span> <span id="reservationScheduleDetails-paxInfant"></span> </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;">
                        <h6 class="mb-0">Schedule Date & Time</h6>
                        <div class="order-details-customer-info pl-2 mb-10">
                            <ul>
                                <li> <span>Schedule Date</span> <span id="reservationScheduleDetails-scheduleDate" class="text-primary"></span> </li>
                                <li> <span>Pick Up Time</span> <span id="reservationScheduleDetails-pickupTime"></span> </li>
                                <li id="containerActivityTime"> <span>Activity Time</span> <span id="reservationScheduleDetails-activityTime"></span> </li>
                            </ul>
                        </div>
                        <h6 class="mb-0">Area</h6>
                        <span id="reservationScheduleDetails-pickupAreaName" class="pl-2"></span>
                        <h6 class="mb-0 mt-10">Pick Up</h6>
                        <span id="reservationScheduleDetails-pickupLocation" class="pl-2"></span>
                        <h6 class="mb-0 mt-10">Hotel</h6>
                        <span id="reservationScheduleDetails-hotelName" class="pl-2"></span>
                        <h6 class="mb-0 mt-10">Remark</h6>
                        <span id="reservationScheduleDetails-remark" class="pl-2"></span>
                    </div>
                    <div class="col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;" id="reservationScheduleDetails-containerDetailCollectPayment">
                        <div class="alert alert-warning pr-20" role="alert">
                            <i class="fa fa-exclamation-triangle" style="font-size: 24px;"></i> This order has a collect payment <b class="text-warning" id="reservationScheduleDetails-detailCollectPayment"></b>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;">
                        <div class="row">
                            <div class="col-lg-4 col-sm-12">
                                <h6>Reception Date & Time</h6>
                                <span id="reservationScheduleDetails-receptionDateTime"></span>
                            </div>
                            <div class="col-lg-4 col-sm-12">
                                <h6>Confirmation Details</h6>
                                <span id="reservationScheduleDetails-confirmationDetails"></span>
                            </div>
                            <div class="col-lg-4 col-sm-12">
                                <h6>Last Status</h6>
                                <span id="reservationScheduleDetails-lastStatus"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div id="slideToUpdateSchedule"></div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="reservationScheduleDetails-idSchedule" name="reservationScheduleDetails-idSchedule" value="">
				<button type="button" class="button button-primary mr-auto" id="btnJobCompleted">Job Completed</button>
				<button type="button" class="button button-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" tabindex="-1" id="modal-scheduleTimePicker" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<form class="modal-content form-horizontal" id="content-scheduleTimePicker">
			<div class="modal-header">
				<h5 class="modal-title" id="title-scheduleTimePicker">Change Schedule Time</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-sm-12"><h6>Pick Schedule Time</h6></div>
					<div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group my-0">
                                    <select id="scheduleTimePicker-optionScheduleTimeHour" name="scheduleTimePicker-optionScheduleTimeHour" class="form-control"></select>
                                </div>
        					</div>
                            <div class="col-sm-6">
                                <div class="form-group my-0">
                                    <select id="scheduleTimePicker-optionScheduleTimeMinute" name="scheduleTimePicker-optionScheduleTimeMinute" class="form-control"></select>
                                </div>
        					</div>
		    			</div>
					</div>
    			</div>
			</div>
			<div class="modal-footer">
				<button class="button button-primary" id="btnConfirmChangeScheduleTime">Change</button>
				<button type="button" class="button button-default" data-dismiss="modal">Close</button>
			</div>
		</form>
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
<style>
    #table-reservationSchedule tbody tr:first-child td {
        border : none;
    }
    #table-reservationSchedule tbody tr td{
        padding: .5rem;
    }
</style>
<script>
	var url = "<?=BASE_URL_ASSETS_JS?>page-module/schedule.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>