<div class="row justify-content-between align-items-center mb-10">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Detail fee<span> / Fee details after order completion base on period</span></h3>
		</div>
	</div>
	<div class="col-12 col-lg-auto mb-10">
		<div class="page-date-range">
            <a class="button button-info button-sm pull-right d-none mt-1" id="excelDataDetailFee" target="_blank" href=""><span><i class="fa fa-file-excel-o"></i>Excel Detail Fee</span></a>
		</div>
	</div>
</div>
<div class="box mb-10">
	<div class="box-body">
        <div class="row">
            <div class="col-lg-2 col-sm-6">
                <div class="form-group">
                    <label for="startDate" class="control-label">Reservation Date</label>
                    <input type="text" class="form-control input-date-single mb-10 text-center" id="startDate" name="startDate" value="<?=date('01-m-Y')?>">
                </div>
            </div>
            <div class="col-lg-2 col-sm-6">
                <div class="form-group">
                    <label for="endDate" class="control-label">.</label>
                    <input type="text" class="form-control input-date-single text-center" id="endDate" name="endDate">
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="form-group">
                    <label for="bookingCodeKeyword" class="control-label">Booking Code</label>
                    <input type="text" class="form-control mb-10" id="bookingCodeKeyword" name="bookingCodeKeyword" placeholder="Type something and push ENTER to search">
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="form-group">
                    <label for="productNameKeyword" class="control-label">Product Name</label>
                    <input type="text" class="form-control mb-10" id="productNameKeyword" name="productNameKeyword" placeholder="Type something and push ENTER to search">
                </div>
            </div>
        </div>
	</div>
</div>
<div class="box">
	<div class="box-body">
        <div class="row">
            <div class="col-lg-8 col-sm-12 mb-10 d-flex">
                <span id="tableDataCountDetailFee" class="align-self-center"></span>
            </div>
            <div class="col-lg-4 col-sm-12 mb-10">
                <div class="row">
                    <div class="col-3 text-right align-self-center px-1">Order By</div>
                    <div class="col-6 px-1">
                        <select id="optionOrderBy" name="optionOrderBy" class="form-control">
                            <option value="1">Activity Date</option>
                            <option value="2">Product Name</option>
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
            <table class="table" id="table-detailFee">
                <thead class="thead-light">
                    <tr>
                        <th width="120">Date</th>
                        <th >Reservation Detail</th>
                        <th >Schedule Detail</th>
                        <th width="280">Ticket Pax</th>
                        <th width="120" class="text-right">Fee</th>
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
                <ul class="pagination" id="tablePaginationDetailFee"></ul>
            </div>
        </div>
	</div>
</div>
<style>
	.order-details-customer-info ul li span:first-child{
		width: 48px;
		margin-right: 8px;
	}
</style>
<script>
	var url = "<?=BASE_URL_ASSETS_JS?>page-module/Fee.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>