<div class="row justify-content-between align-items-center mb-10">
	<div class="col-12 col-lg-auto mb-20">
		<div class="page-heading">
			<h3 class="title">Product List <span> / Product detail information and nett price</span></h3>
		</div>
	</div>
</div>
<div class="box">
    <div class="box-body px-3">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="keywordSearch" class="control-label">Search Product</label>
                    <input type="text" class="form-control" id="keywordSearch" name="keywordSearch" value="" placeholder="Type something and press ENTER to search">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="box mt-10">
    <div class="box-body px-0 py-0 responsive-table-container">
        <div class="row">
            <div class="col-12">
                <table class="table" id="table-dataProductList">
                    <thead class="thead-light">
                        <tr>
                            <th width="30" class="text-left">#</th>
                            <th>Product Name</th>
                            <th width="150">Pax Range</th>
                            <th width="120" class="text-right">Adult</th>
                            <th width="120" class="text-right">Child</th>
                            <th width="120" class="text-right">Infant</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
	var url = "<?=BASE_URL_ASSETS_JS?>page-module/productList.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>