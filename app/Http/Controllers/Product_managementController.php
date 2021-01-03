<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product_info;
use App\Models\Sales_info;
use App\Models\Attachments;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\CategoryRequest;


class Product_managementController extends Controller
{

    /**
     *  The redirect instance
     */
    protected $skey;
    protected $sfst_cate;
    protected $ssnd_cate;
    protected $redirectUrl;
    protected $searchData;
    /**
     *  Create a new controller instance
     *
     * @param CategoryRequest $categoryRequest
     * @return void
     */
    public function __construct(CategoryRequest $categoryRequest)
    {

        //redirect 변수
        echo "test";
        $this->sfst_cate = $categoryRequest->sfst_cate;
        $this->ssnd_cate = $categoryRequest->ssnd_cate;
        $this->skey = $categoryRequest->skey;
        $this->redirectUrl = 'sfst_cate='.$this->sfst_cate.'&ssnd_cate='.$this->ssnd_cate.'&skey='.$this->skey;
        $this->searchData = $categoryRequest;
        //페이지스킨변경
        \Illuminate\Pagination\Paginator::useBootstrap();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product_info $product_info)
    {
        $this->sfst_cate=='All' ? $sfst_cate='' : $sfst_cate=$this->sfst_cate;
        $this->ssnd_cate=='All' ? $ssnd_cate='' : $ssnd_cate=$this->ssnd_cate;
        $skey= $this->skey;

        $list = $product_info->leftJoin('sales_info','product_info.product_code', '=', 'sales_info.product_code')
        ->where('sales_info.isnew','=','new')
        ->when($sfst_cate, function($query,$sfst_cate){
            return $query->where('product_info.fst_cate', 'like', "%$sfst_cate%");
        })
        ->when($ssnd_cate, function($query,$ssnd_cate){
            return $query->where('product_info.snd_cate', 'like', "%$ssnd_cate%");
        })
        ->when($skey, function($query,$skey){
            return $query->where('product_info.eng_name', 'like', "%$skey%");
        })
        ->orderby('product_info.eng_name')
        ->paginate(3);

        return view('productManagement.list')->with([
            'list'=>$list,
            'searchData'=> $this->searchData,
            'redirectUrl'=>$this->redirectUrl
        ]);
        //return view('welcome');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $prdRequest, Product_info $product_info, Sales_info $sales_info, Attachments $attachments)
    {

        $product_info->create($prdRequest->all());

        $key = $product_info->latest()->first()->product_code;

        $sales_info->create([
            'product_code' => $key,
            'eng_name' => $prdRequest->eng_name,
            'initial_stock' => $prdRequest -> stock,
            'stock' => $prdRequest->stock,
            'isnew' => 'new'
        ]);

        if($prdRequest->hasFile('file')){

            $files = $prdRequest->file;
            foreach ($files as $file){
                $originFilename = $file->getClientOriginalName();
                $filename = \Str::random().filter_var($file->getClientOriginalName(),FILTER_SANITIZE_URL);

                $attachments->create([
                    'product_code' => $key,
                    'filename' => $filename,
                    'originfilename' => $originFilename,
                    'byte' => $file->getSize(),
                    'mime' => $file->getClientMimeType()
                ]);
                $file->storeAs('product_management',$filename);
            }
        }

        return redirect('/wine/adm/product_managements?'.$this->redirectUrl)->with('flash_message', '작성하신 글이 저장되었습니다.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productManagement.create')->with([
            'redirectUrl'=>$this->redirectUrl,
            'searchData'=>$this->searchData
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $product_code
     * @return \Illuminate\Http\Response
     */
    public function edit($product_code, Product_info $product_info, Attachments $attachments)
    {
        $products = $product_info
            ->leftJoin('sales_info','product_info.product_code', '=', 'sales_info.product_code')
            ->where('sales_info.product_code','=',$product_code)
            ->where('sales_info.isnew','=','new')
            ->orderby('product_info.eng_name')
            ->get();
        $attachment_info = $attachments->where('product_code',$product_code)->get();
        return view('productManagement.edit')->with([
            'products'=>$products,
            'attachments'=>$attachment_info,
            'product_code'=>$product_code,
            'redirectUrl'=>$this->redirectUrl,
            'searchData'=>$this->searchData
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $product_code
     * @return \Illuminate\Http\Response
     */
    public function update($product_code, ProductRequest $prdRequest, Product_info $product_info, Sales_info $sales_info, Attachments $attachments)
    {
        $originPrdCnt = $attachments->where('product_code',$product_code)->get()->all();
        $originPrdCnt = count($originPrdCnt);//기존첨부파일 개수
        $newPrdCnt = isset($prdRequest->file) ? count($prdRequest->file) : 0;//업로드할 첨부파일 개수
        $delPrdCnt = isset($prdRequest->delFile) ? count($prdRequest->delFile) : 0;//삭제할 첨부파일 개수

        if(($originPrdCnt+$newPrdCnt-$delPrdCnt)>2){//저장할 수 있는 첨부파일 총 개수
            return back()->with('flash_message', '글이 저장되지 않았습니다.')->withInput();
        }

        $product_info->where('product_code', $product_code)->update([
            'fst_cate' => $prdRequest->fst_cate,
            'snd_cate' => $prdRequest->snd_cate,
            'eng_name' => $prdRequest->eng_name,
            'kor_name' => $prdRequest->kor_name,
            'origin' => $prdRequest->origin,
            'type' => $prdRequest->type,
            'personality' => $prdRequest->personality,
            'in_price' => $prdRequest->in_price,
            'out_price' => $prdRequest->out_price,
            'descr' => $prdRequest->descr
        ]);

        $sales_info->where('product_code', $product_code)
            ->update([
                'eng_name'=>$prdRequest->eng_name,
                'stock'=>$prdRequest->stock
            ]);

        if($prdRequest->hasFile('file') || $prdRequest->delFile){

            if($prdRequest->delFile){
                foreach ($prdRequest->delFile as $delFile){
                    $attachments->destroy($delFile);
                }
            }

            if($prdRequest->hasFile('file')){
                $files = $prdRequest->file('file');
                foreach ($files as $file){
                    $originFilename = $file->getClientOriginalName();
                    $filename = \Str::random().filter_var($file->getClientOriginalName(),FILTER_SANITIZE_URL);

                    $attachments->create([
                        'product_code' => $product_code,
                        'filename' => $filename,
                        'originfilename' => $originFilename,
                        'byte' => $file->getSize(),
                        'mime' => $file->getClientMimeType()
                    ]);
                    $file->storeAs('product_management',$filename);
                }
            }
        }
        return redirect('/wine/adm/product_managements?'.$this->redirectUrl);
    }

    /**
     * Remove the specified resource from storage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product_info $product_info, Sales_info $sales_info, Attachments $attachments, Request $request)
    {
        $selectdel = $request->selectdel;
        for($i=0; $i<count($selectdel); $i++){
            $isDel = $attachments->where('product_code',$selectdel[$i])->get();

            if(count($isDel)>0){
                $attachments->where('product_code',$selectdel[$i])->delete();
            }
        }
        $sales_info->destroy($selectdel);

        $product_info->destroy($request->selectdel);
        return redirect('/wine/adm/product_managements?'.$this->redirectUrl);
    }

}
