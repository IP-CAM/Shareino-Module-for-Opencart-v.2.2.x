<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <a href="index.php?route=extension/module&token=<?php echo $token; ?>" data-toggle="tooltip" title="بازگشت به صفحه قبل" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>

        <div class="alert alert-dismissable" id="MessageBox" role="alert" hidden>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <p id="MessageText"></p>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i>تنظیمات ماژول شرینو</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-sm-8">
                        <ul>
                            <li>ابتدا توکن خود را از سایت <a href="https://www.ShareINO.ir" target="_blank" title="شرینو - شبکه اجتماعی خرید">شرینو</a> دریافت کنید.</li>
                            <li>در صورت بروز هر گونه خطا ابتدا از صحت توکن خود اطمینان حاصل کنید</li>
                            <li>کالاهای شما بعد از دریافت تصاویر در سایت شرینو قابل مشاهده میباشند</li>
                            <li>در صورت بروز هر گونه مشکل یا ابهامی می‌توانید با کارشناسان ما در ارتباط باشید</li>
                        </ul>
                    </div>
                    <div class="col-sm-4">
                        <a href="https://www.ShareINO.ir" target="_blank" title="شرینو -شبکه اجتماعی خرید"><img class="logo" src="view/image/shareino-logo.png" alt="شرینو - شبکه اجتماعی خرید" /></a>
                    </div>
                </div>
            </div> <!-- ./ShareINO  -->
            <hr/>
            <div class="panel-body">
                <div class="form-group">
                    <form action="<?php echo $action; ?>" method="post" class="form-horizontal" id="shareino_out_of_stock" data-token="<?php echo $token ?>">
                        <label class="col-sm-2 control-label" for="input-name">درصورت اتمام موجودی</label>
                        <div class="col-sm-9">
                            <select name="shareino_out_of_stock" class="form-control">
                                <option value="0" <?php if($shareino_out_of_stock== 0) echo'selected';?>>رد سفارش</option>
                                <option value="1" <?php if($shareino_out_of_stock== 1) echo'selected';?>>امکان ثبت سفارش</option>
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-primary"><i id="state-icon-product" class="fa fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div> <!-- ./out of stock  -->
            <hr/>
            <div class="panel-body">
                <div class="form-group">
                    <form action="<?php echo $action; ?>" method="post" class="form-horizontal sync-products" data-token="<?php echo $token ?>" data-operation="0">
                        <label class="col-sm-2 control-label" for="input-name">ارسال کالا ها</label>
                        <div class="col-sm-9">
                            <p class="shareino-text">اگر اولین بار میباشد که ماژول را نصب کرده اید یا اینکه کالاهای سایت خود را تغییر داده اید آن را برای شرینو ارسال کنید</p>
                        </div>
                        <div class="col-sm-1">
                            <button id="state-btn-product" type="submit" class="btn btn-primary" title="ارسال کالا های سایت شما"><i id="state-icon-product" class="fa fa-send"></i></button>
                        </div>
                        <div class="col-sm-10 col-sm-offset-1">
                            <div class="text-center" id="progress" hidden>
                                <p class="label label-default" id="progressText"></p>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped active" id="sync-progress" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> <!-- ./products  -->
            <hr/>
            <div class="panel-body">
                <div class="form-group">
                    <form action="<?php echo $action; ?>" method="post" class="form-horizontal sync-category"  data-token="<?php echo $token ?>" data-operation="0">
                        <label class="col-sm-2 control-label" for="input-name">ارسال دسته بندی ها</label>
                        <div class="col-sm-9">
                            <p class="shareino-text">اگر اولین بار میباشد که ماژول را نصب کرده اید یا اینکه دسته بندی های سایت خود را تغییر داده اید آن را برای شرینو ارسال کنید</p>
                        </div>
                        <div class="col-sm-1">
                            <button  id="state-btn-category" type="submit" class="btn btn-primary" title="ارسال دسته بندی های سایت شما"><i id="state-icon-category" class="fa fa-send"></i></button>
                        </div>
                    </form>
                </div>
            </div> <!-- ./category  -->
            <hr/>
            <div class="panel-body">
                <div class="form-group">
                    <form action="<?php echo $action; ?>" method="post" class="form-horizontal shareino-form-token" id="shareino_api_token" data-token="<?php echo $token ?>">
                        <label class="col-sm-2 control-label" for="input-name">توکن دریافتی از شرینو</label>
                        <div class="col-sm-9">
                            <input type="text" id="shareino_api_token" name="shareino_api_token" value="<?php echo $shareino_api_token; ?>" placeholder="توکن فروشگاهی خود در سایت شرینو را در اینجا وارد کنید." class="form-control"/>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-primary" title="ذخیره توکن"><i class="fa fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div><!-- ./token  -->
            <div class="panel-body">
                <div class="form-group">
                    <form action="<?php echo $action; ?>" method="post" class="form-horizontal shareino-form-token" id="shareino_api_token" data-token="<?php echo $token ?>">
                        <label class="col-sm-2 control-label" for="input-name">لینک کرون جاب</label>
                        <div class="col-sm-10">
                            <input type="text"  dir="ltr" value='/usr/bin/wget -O - -q <?php echo $shareino_token_frontend ?>' class="form-control" readonly="true"/>
                        </div>
                    </form>
                </div>
            </div><!-- ./token frontend  -->

            <div class="panel-footer"></div>
        </div>
    </div>
</div>
<style type="text/css">.shareino-text{font-size:14px;padding:8px}.logo{width:220px;float:left}ul{padding:0}</style>
<script>
    $(function() {
        var messageBox = $("#MessageBox");
        var messageText = $("#MessageText");
        var token = $(".sync-category").attr('data-token');
        var operation = null;
        var stop = false;

        $('.sync-category').on('submit', function(e) {
            e.preventDefault();
            messageBox.hide();
            operation = $('.sync-category').attr('data-operation');
            operation === '1' ? startSync('#state-btn-category', '#state-icon-category', '.sync-category') : stopSync('#state-btn-category', '#state-icon-category', '.sync-category');
            if (stop) {
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: 'index.php?route=module/shareino/syncCategory&token=' + token,
                    data: {
                        ajax: true,
                        controller: 'shareino',
                        action: 'syncCategory',
                        id: 1
                    },
                    success: function(data, textStatus, jqXHR) {
                        startSync('#state-btn-category', '#state-icon-category', '.sync-category');
                        message(data.status, data.message);
                    },
                    error: function(data) {
                        message(false, data.message);
                    }
                });
            }
        });

        var split = 50;
        var pageNumber = 1;
        var countProduct = <?php echo $countProduct;?>;
        var pageCount = Math.ceil(countProduct / split);
        var progress = $("#progress");
        var submitProgress = $("#sync-progress");

        $('.sync-products').on('submit', function(e) {
            e.preventDefault();
            messageBox.hide();
            progress.show(500);
            operation = $('.sync-products').attr('data-operation');
            submitProgress.show();
            SyncProducts();
        });

        function SyncProducts() {

            if (countProduct === 0) {
                message(true, 'همه محصولات ارسال شده است.');
                startSync('#state-btn-product', '#state-icon-product', '.sync-products');
                return;
            }

            if (pageNumber > pageCount) {
                message(true, 'همه محصولات به سایت شرینو ارسال شد.');
                startSync('#state-btn-product', '#state-icon-product', '.sync-products');
                return;
            }

            operation === '1' ? startSync('#state-btn-product', '#state-icon-product', '.sync-products') : stopSync('#state-btn-product', '#state-icon-product', '.sync-products');
            if (stop) {
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: 'index.php?route=module/shareino/SyncProducts&token=' + token,
                    data: {
                        ajax: true,
                        controller: 'shareino',
                        action: 'SyncProducts',
                        token: token,
                        pageNumber: pageNumber,
                        split: split
                    },
                    success: function(data) {
                        if (data.status === false) {
                            if (data.code === 429) {
                                messageText.html(data.message);
                                messageBox.addClass('alert-warning');
                                messageBox.show(500);
                                setTimeout(SyncProducts, 61 * 1000);
                            } else {
                                message(data.status, data.message);
                            }
                        } else {
                            setPercentage();
                            SyncProducts();
                        }
                    },
                    error: function(data) {
                        message(false, data.message);
                    }
                });
            }
            pageNumber++;
        }

        function setPercentage() {
            var percentage = Math.round((100 * (pageNumber * split)) / countProduct);
            percentage = percentage > 100 ? 100 : percentage;
            submitProgress
                    .css('width', percentage + "%")
                    .attr('aria-valuemin', percentage + "%")
                    .html(percentage + '%');
        }

        function stopSync(btn, i, form) {
            $(btn).addClass('btn-danger');
            $(i).removeClass('fa-send');
            $(i).addClass('fa-stop');
            $(form).attr('data-operation', '1');
            stop = true;
        }

        function startSync(btn, i, form) {
            $(btn).removeClass('btn-danger');
            $(i).addClass('fa-send');
            $(form).attr('data-operation', '0');
            stop = false;
        }
        function message(status, message) {
            messageText.html(message);
            messageBox.show(500);
            messageBox.addClass(status ? 'alert-success' : 'alert-danger');
        }
    });
</script>

<?php echo $footer; ?>
