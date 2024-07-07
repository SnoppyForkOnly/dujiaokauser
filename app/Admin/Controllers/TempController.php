<?php

namespace App\Admin\Controllers;

use App\Admin\Charts\DashBoard;
use App\Admin\Charts\PayoutRateCard;
use App\Admin\Charts\PopularGoodsCard;
use App\Admin\Charts\SalesCard;
use App\Admin\Charts\SuccessOrderCard;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Row;

class TempController extends Controller
{

    public function index(Content $content)
    {
        return $content
            ->header(admin_trans('dujiaoka.dashboard'))
            ->description(admin_trans('dujiaoka.dashboard_description'))
            ->body(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->row(self::title());
                    $column->row(new DashBoard());
                });

                $row->column(6, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(6, new SuccessOrderCard());
                        $row->column(6, new PayoutRateCard());
                    });

                    $column->row(new SalesCard());
                });
            });
    }

    public static function title()
    {
        return view('admin.dashboard.title');
    }
    
protected function getCurrentTemplateName()
{
    // 使用 dujiaoka_config_get 函数获取当前模板名称，如果没有设置则默认为 'hyper'
    // 注意，这里假设 'template' 是存储模板名称的键
    $templateName = dujiaoka_config_get('templates', 'hyper');
    return $templateName;
}


protected function getCurrentFile()
{
    $FileName = dujiaoka_config_get('files', 'home.blade.php');
    // 调试信息
 
    return $FileName;
}



protected function getBanFile()
{
    $BanName = dujiaoka_config_get('bqfiles', '_footer.blade.php');
    // 调试信息
 
    return $BanName;
}




public function editHome(Content $content)
{
    $templateName = $this->getCurrentTemplateName();
    $FileName = $this->getCurrentFile() ?? 'home.blade.php';
  $filePath = resource_path("views/{$templateName}/static_pages/{$FileName}");
    $fileContent = file_get_contents($filePath);

  return $content
    ->header("编辑{$FileName}")
    ->description("直接编辑{$templateName}/static_pages/{$FileName}文件")
    ->body(view('admin.edit_home', ['fileContent' => $fileContent]));

}




    /**
     * 处理对特定文件的更新请求。
     */
public function updateHome(Request $request)
{
    $templateName = $this->getCurrentTemplateName();
    $FileName = $this->getCurrentFile() ?? 'home.blade.php';
    $filePath = resource_path("views/{$templateName}/static_pages/{$FileName}");
    $newContent = $request->input('fileContent');
    
    // 生成备份文件名，格式为：模板名称_文件名_时间戳.backup
    $backupFileName = resource_path("views/{$templateName}/static_pages/" . "{$FileName}_" . date('Y-m-d_H-i-s') . ".backup");
    
    // 确保有原文件内容
    if (file_exists($filePath)) {
        // 将原文件内容复制到备份文件
        $originalContent = file_get_contents($filePath);
        file_put_contents($backupFileName, $originalContent);
    }

    // 进行必要的安全和验证检查...
    // 然后写入新内容到原文件
    file_put_contents($filePath, $newContent);

    admin_success("'操作成功', '{$FileName}已成功更新，并已自动备份原文件在{$backupFileName}！'");
    return redirect(admin_url('edit_home'));
}


public function editYetou(Content $content)
{
    $templateName = $this->getCurrentTemplateName();
    $BanName = $this->getBanFile() ?? '_footer.blade.php';
  $filePath = resource_path("views/{$templateName}/layouts/{$BanName}");
    $fileContent = file_get_contents($filePath);

  return $content
    ->header("编辑{$BanName}")
    ->description("直接编辑{$templateName}/layouts/{$BanName}文件")
    ->body(view('admin.edit_yetou', ['fileContent' => $fileContent]));

}


public function updateYetou(Request $request)
{
    $templateName = $this->getCurrentTemplateName();
     $BanName = $this->getBanFile() ?? '_footer.blade.php';
     $filePath = resource_path("views/{$templateName}/layouts/{$BanName}");
    $newContent = $request->input('fileContent');
    
    // 生成备份文件名，格式为：模板名称_文件名_时间戳.backup
    $backupFileName = resource_path("views/{$templateName}/static_pages/" . "{$$BanName}_" . date('Y-m-d_H-i-s') . ".backup");
    
    // 确保有原文件内容
    if (file_exists($filePath)) {
        // 将原文件内容复制到备份文件
        $originalContent = file_get_contents($filePath);
        file_put_contents($backupFileName, $originalContent);
    }

    // 进行必要的安全和验证检查...
    // 然后写入新内容到原文件
    file_put_contents($filePath, $newContent);

    admin_success("'操作成功', '{$$BanName}已成功更新，并已自动备份原文件在{$backupFileName}！'");
    return redirect(admin_url('edit_home'));
}






}