<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Reservation <span> / List of reservations received and confirmed</span></h3>
		</div>
	</div>
	<div class="col-12 col-lg-auto mb-10">
		<div class="page-date-range">
            <a class="button button-info button-sm pull-right d-none mt-1" id="excelDataConfirmedReservation" target="_blank" href=""><span><i class="fa fa-file-excel-o"></i>Excel Detail Reservartion</span></a>
		</div>
	</div>
</div>
<ul class="nav nav-tabs mb-20" id="tabsPanel">
    <li class="nav-item" id="liTabUnconfirmedReservation"><a class="nav-link active" data-toggle="tab" href="#unconfirmedReservationTab" id="navLinkUnconfirmedReservationTab"><i class="fa fa-file-text"></i> Unconfirmed</a></li>
    <li class="nav-item" id="liTabConfirmedReservation"><a class="nav-link" data-toggle="tab" href="#confirmedReservationTab"><i class="fa fa-calendar"></i> Confirmed</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="unconfirmedReservationTab">
        <div class="box mb-10">
            <div class="box-body px-3">
                <div class="row">
                    <div class="col-lg-3 col-sm-5">
                        <div class="form-group">
                            <label for="startActivityDateUnconfirm" class="control-label">Activity Date</label>
                            <input type="text" class="form-control input-date-single mb-10 text-center" id="startActivityDateUnconfirm" name="startActivityDateUnconfirm">
                        </div>
                    </div>
                    <div class="col-lg-9 col-sm-7">
                        <div class="form-group">
                            <label for="searchKeywordUnconfirm" class="control-label">Search by Customer Name / Code Booking / Job title</label>
                            <input type="text" class="form-control" id="searchKeywordUnconfirm" name="searchKeywordUnconfirm" placeholder="Type something and press ENTER to search">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box mb-10">
            <div class="box-body px-3 responsive-table-container">
                <div class="row">
                    <div class="col-lg-8 col-sm-12 mb-10 d-flex">
                        <span id="tableDataCountUnconfirmed" class="align-self-center"></span>
                    </div>
                    <div class="col-lg-4 col-sm-12 mb-10">
                        <div class="row">
                            <div class="col-3 text-right align-self-center px-1">Order By</div>
                            <div class="col-6 px-1">
                                <select id="optionUnconfirmedOrderBy" name="optionUnconfirmedOrderBy" class="form-control">
                                    <option value="1">Activity Date</option>
                                    <option value="2" selected>Reception Date</option>
                                </select>
                            </div>
                            <div class="col-3 px-1">
                                <select id="optionUnconfirmedOrderType" name="optionUnconfirmedOrderType" class="form-control">
                                    <option value="ASC">Asc</option>
                                    <option value="DESC" selected>Desc</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-10 px-0">
                        <table class="table" id="table-unconfirmedReservation">
                            <thead class="thead-light">
                                <tr>
                                    <th width="220">Date Time</th>
                                    <th width="280">Booking Details</th>
                                    <th>Customer</th>
                                    <th width="250">Additional Info</th>
                                    <th width="350">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th colspan="5" class="text-center">No data is shown</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 mb-10">
                        <ul class="pagination" id="tablePaginationUnconfirmedReservation"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="confirmedReservationTab">
        <div class="box mb-10">
            <div class="box-body responsive-table-container">
    			<div class="row">
                    <div class="col-lg-6 col-sm-6">
                        <div class="form-group">
                            <label for="optionReservationStatus" class="control-label">Reservation Status</label>
                            <select id="optionReservationStatus" name="optionReservationStatus" class="form-control">
                                <option value="">All Status</option>
                                <option value="1">Scheduled</option>
                                <option value="2">On Process</option>
                                <option value="3">Done</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-3">
                        <div class="form-group">
                            <label for="startActivityDate" class="control-label">Activity Date</label>
                            <input type="text" class="form-control input-date-single mb-10 text-center" id="startActivityDate" name="startActivityDate" value="<?=date('01-m-Y')?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-3">
                        <div class="form-group">
                            <label for="endActivityDate" class="control-label">.</label>
                            <input type="text" class="form-control input-date-single text-center" id="endActivityDate" name="endActivityDate" value="<?=date('d-m-Y')?>">
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="form-group">
                            <label for="bookingCode" class="control-label">Booking Code</label>
                            <input type="text" class="form-control mb-10" id="bookingCode" name="bookingCode">
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="form-group">
                            <label for="customerName" class="control-label">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" name="customerName">
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                        <div class="form-group">
                            <label for="locationName" class="control-label">Customer Hotel / Pick Up / Drop Off</label>
                            <input type="text" class="form-control" id="locationName" name="locationName">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-body px-3">
                <div class="row">
                    <div class="col-lg-8 col-sm-12 mb-10 d-flex">
                        <span id="tableDataCountConfirmed" class="align-self-center"></span>
                    </div>
                    <div class="col-lg-4 col-sm-12 mb-10">
                        <div class="row">
                            <div class="col-3 text-right align-self-center px-1">Order By</div>
                            <div class="col-6 px-1">
                                <select id="optionConfirmedOrderBy" name="optionConfirmedOrderBy" class="form-control">
                                    <option value="1">Activity Date</option>
                                    <option value="2">Reception Date</option>
                                    <option value="3">Confirmation Date</option>
                                </select>
                            </div>
                            <div class="col-3 px-1">
                                <select id="optionConfirmedOrderType" name="optionConfirmedOrderType" class="form-control">
                                    <option value="ASC">Asc</option>
                                    <option value="DESC">Desc</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-10 px-0">
                        <table class="table" id="table-confirmedReservation">
                            <thead class="thead-light">
                                <tr>
                                    <th width="220">Date Time</th>
                                    <th width="280">Booking Details</th>
                                    <th>Customer</th>
                                    <th width="250">Additional Info</th>
                                    <th width="350">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th colspan="6" class="text-center">No data is shown, please apply filter first</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 mb-10">
                        <ul class="pagination" id="tablePaginationConfirmedReservation"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="modal-confirmSchedule" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<form class="modal-content form-horizontal" id="content-confirmSchedule">
			<div class="modal-header">
				<h5 class="modal-title" id="title-confirmSchedule">Choose Time Schedule to Confirm</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body mr-10 ml-10">
				<div class="row">
					<div class="col-lg-9 col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;">
                        <div class="order-details-customer-info">
                            <ul>
                                <li> <span>Schedule Date</span> <span id="confirmSchedule-scheduleDate"></span> </li>
                                <li> <span>Pick Up Time</span> <span id="confirmSchedule-pickupTime"></span> </li>
                                <li> <span>Reservation Title</span> <span id="confirmSchedule-reservationTitle"></span> </li>
                                <li> <span>Product</span> <span id="confirmSchedule-productName"></span> </li>
                                <li> <span>Customer Name</span> <span id="confirmSchedule-customerName"></span> </li>
                                <li> <span>Remark</span> <span id="confirmSchedule-remark"></span> </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-12 mb-10 pb-10" style="border-bottom: 1px solid #e0e0e0;">
                        <h6>Pax</h6>
                        <div class="order-details-customer-info">
                            <ul>
                                <li class="ml-10"> <span>Adult</span> <span id="confirmSchedule-paxAdult"></span> </li>
                                <li class="ml-10"> <span>Child</span> <span id="confirmSchedule-paxChild"></span> </li>
                                <li class="ml-10"> <span>Infant</span> <span id="confirmSchedule-paxInfant"></span> </li>
                            </ul>
                        </div>
                    </div>
					<div class="col-lg-4 col-sm-12">
                        <h6>Requested Time Slot</h6>
                        <h5 id="confirmSchedule-bookingTime"></h5>
                    </div>
					<div class="col-lg-8 col-sm-12">
                        <div class="row">
        					<div class="col-sm-12"><h6>Pick Schedule Time Slot</h6></div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group my-0">
                                    <select id="confirmSchedule-optionScheduleTimeHour" name="confirmSchedule-optionScheduleTimeHour" class="form-control"></select>
                                </div>
        					</div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group my-0">
                                    <select id="confirmSchedule-optionScheduleTimeMinute" name="confirmSchedule-optionScheduleTimeMinute" class="form-control"></select>
                                </div>
        					</div>
		    			</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="confirmSchedule-idReservationDetails" name="confirmSchedule-idReservationDetails" value="">
				<input type="hidden" id="confirmSchedule-idSchedule" name="confirmSchedule-idSchedule" value="">
				<button class="button button-primary" id="btnConfirmSchedule">Confirm Schedule</button>
				<button type="button" class="button button-default" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<script>
	var url = "<?=BASE_URL_ASSETS_JS?>page-module/reservation.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>