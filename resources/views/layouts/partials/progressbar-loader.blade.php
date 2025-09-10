<div class="progress-loader-wrapper" style="display: none;">
    <div class="loader">
        <div>
            <div class="progress">
                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" class="progress-bar progress-bar-striped" id="mainProgrssbar" role="progressbar" style="width: 0%">
                    <span class="progpercent">
                        {{ trans('layout.loader.0') }}
                    </span>
                </div>
            </div>
            <p class="status-text">
                {{ trans('layout.loader.uploading') }}
            </p>
        </div>
    </div>
</div>
<style type="text/css">
    .progress-loader-wrapper{
        z-index: 99999999;position: fixed;top: 0;left: 0;bottom: 0;right: 0;width: 100%;height: 100%;overflow: hidden;background: #fff;flex-wrap: wrap;display: flex;text-align: center;align-self: center;justify-content: center;
    }
    .progress-loader-wrapper .loader{
        text-align: center;align-self: center;justify-content: center;
        width: 80%;
    }
</style>