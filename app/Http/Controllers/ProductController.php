<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportadorImport;
use App\Imports\CSVImport;

class ProductController extends AppBaseController
{
    /** @var ProductRepository $productRepository*/
    private $productRepository;
    private $regProduct = [];
    // private $store_import = null;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepository = $productRepo;
    }

    /**
     * Display a listing of the Product.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // $products = $this->productRepository->all();
        $products = \App\Models\Product::where('id_concession', auth()->user()->id_concession)->get();

        return view('products.index')
            ->with('products', $products);
    }

    /**
     * Show the form for creating a new Product.
     *
     * @return Response
     */
    public function create()
    {
        $category_products = \App\Models\Category_product::where('id_concession', auth()->user()->id_concession)->get();
        return view('products.create')->with('category_products', $category_products);
    }

    /**
     * Store a newly created Product in storage.
     *
     * @param CreateProductRequest $request
     *
     * @return Response
     */
    public function store(CreateProductRequest $request)
    {
        $input = $request->all();

        $product = \App\Models\Product::create([
            'name' => $input['name'],
            'description' => $input['description'],
            'code' => $input['code'],
            // 'stock' => $input['stock'],
            'id_category' => $input['id_category'],
            'id_concession' => auth()->user()->id_concession,
        ]);
        
        \App\Models\Log::create([
            'content' => 'Registro de Producto: '.$product->code.' - '.$product->name, 
            'activity' => 'Creación', 
            'id_user' => auth()->user()->id, 
            'id_concession' => auth()->user()->id_concession
        ]);

        Flash::success('Product guardado exitosamente.');

        return redirect(route('products.index'));
    }

    /**
     * Display the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products.index'));
        }

        return view('products.show')->with('product', $product);
    }

    /**
     * Show the form for editing the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $product = $this->productRepository->find($id);
        $category_products = \App\Models\Category_product::where('id_concession', auth()->user()->id_concession)->get();

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products.index'));
        }

        return view('products.edit')->with('product', $product)->with('category_products', $category_products);
    }

    /**
     * Update the specified Product in storage.
     *
     * @param int $id
     * @param UpdateProductRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateProductRequest $request)
    {
        $input = $request->all();
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products.index'));
        }

        // $product = $this->productRepository->update($request->all(), $id);
        $product->name = $input['name'];
        $product->description = $input['description'];
        $product->code = $input['code'];
        $product->stock = $input['stock'];
        $product->id_category = $input['id_category'];
        $product->save();

        \App\Models\Log::create([
            'content' => 'Edición de Producto: '.$product->code.' - '.$product->name, 
            'activity' => 'Edición', 
            'id_user' => auth()->user()->id, 
            'id_concession' => auth()->user()->id_concession
        ]);

        Flash::success('Product updated successfully.');

        return redirect(route('products.index'));
    }

    /**
     * Remove the specified Product from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products.index'));
        }

        $this->productRepository->delete($id);

        \App\Models\Log::create([
            'content' => 'Eliminación de Producto: '.$product->code.' - '.$product->name, 
            'activity' => 'Eliminación', 
            'id_user' => auth()->user()->id, 
            'id_concession' => auth()->user()->id_concession
        ]);

        Flash::success('Product deleted successfully.');

        return redirect(route('products.index'));
    }

    public function getProduct(Request $request){
        $input = $request->all();
        $product_store = \App\Models\Product_Store::where('id_product', $input['id_product'])->where('id_store', $input['id_store'])->first();
        
        // QUE PASA CUANDO SE ENCUENTRAN 2 PRODUCTOS CON EL MISMO ID EN LA MISMA BODEGA?
        $product = \App\Models\Product::make();
        $product->id = $product_store->id_product;
        $product->name = $product_store->product->name;
        $product->description = $product_store->product->description;
        $product->code = $product_store->product->code;
        $product->stock = $product_store->stock;
        $product->store = $product_store->id_store;
        if($product){
            return view('modal_inventario')->with('product', $product);
        }
        return Response::json(false);
    }

    public function storeModal(Request $request){
        $input = $request->all();
        $product_store = \App\Models\Product_Store::where('id_product', $input['id_product'])->where('id_store', $input['id_store'])->first();
        if($product_store->product->id_concession == auth()->user()->id_concession){
            $product_store->stock = $input['stock_product'];
            $product_store->save();
            return redirect(route('home'));
        }
        return 'el producto no pertenece a la concesion del usuario';
    }

    public function export_products(Request $request){
        $input = $request->all();
        // return Excel::download(new ConsolidadaExport('.xlsx');
    }


    // ************************************************************************************************
    // ************************************************************************************************
    
    // IMPORTAR PRODUCTOS

    public function index_importar_product(Request $request){
        $input = $request->all();
        $warehouses = \App\Models\Store::where('id_concession', auth()->user()->id_concession)->get();
        return view('products.importar')->with('warehouses', $warehouses);
    }

    public function import_products(Request $request){
        $input = $request->all();

        if($input['archivo']->extension() != 'txt')
            $arrayProductos = Excel::toArray(new ImportadorImport(), $input['archivo'])[0];
        else
            $arrayProductos = Excel::toArray(new CSVImport(), $input['archivo'])[0];
        $this->setProducts($arrayProductos);
        // dd($this->regProduct);
        $this->registrarBD($input['id_store']);
        return redirect(route('products.index_importar'));
        // dd('registros exitosos');
    }

    public function setProducts($products){
        $nmb_columnas = $products[0];
        unset($products[0]);
        
        foreach ($products as $key => $product) {
            $this->regProduct[$key]['codigo'] = $product[0];
            $this->regProduct[$key]['nombre'] = $product[1];
            $this->regProduct[$key]['cantidad'] = $product[2];
            $this->regProduct[$key]['ubicaciones'] = explode('-',$product[3]);
            if(\App\Models\Product::where('code', $product[0])->where('id_concession', auth()->user()->id_concession)->exists()){
                $producto = \App\Models\Product::where('code', $product[0])->where('id_concession', auth()->user()->id_concession)->first();
                $this->regProduct[$key]['id_producto'] = $producto->id;
            }
            if(\App\Models\Category_product::where('name', $product[4])->where('id_concession', auth()->user()->id_concession)->exists()){
                $this->regProduct[$key]['categoria'] = $product[4];
            }
            else{
                dd('no existe la categoria: '. $product[4]);
            }
        }
    }

    public function registrarBD($id_store){
        try {
            \DB::beginTransaction();
            foreach ($this->regProduct as $key => $product) {
                $categoria = \App\Models\Category_product::where('name', $product['categoria'])->where('id_concession', auth()->user()->id_concession)->first();
                $store = \App\Models\Store::find($id_store);
                if($categoria && $store){
                    if(!isset($product['id_producto'])){
                        $nuevo_producto = \App\Models\Product::create([
                            'name' => $product['nombre'],
                            'description' => 'test_desc',
                            'code' => $product['codigo'],
                            // 'stock' => $input['stock'],
                            'id_category' => $categoria->id,
                            'id_concession' => auth()->user()->id_concession,
                        ]);
                    }
                    else{
                        $nuevo_producto = \App\Models\Product::where('code', $product['codigo'])->where('id_concession', auth()->user()->id_concession)->first();
                    }
                    // dump($categoria && $store);
                    if(\App\Models\Product_Store::where('id_product', $nuevo_producto->id)->where('id_store', $store->id)->exists()){
                        $store_product = \App\Models\Product_Store::where('id_product', $nuevo_producto->id)->where('id_store', $store->id)->first();
                        $store_product->stock = $product['cantidad'];
                        $store_product->save();
                    }
                    else{
                        $store->products()->attach($nuevo_producto->id, ['stock' => $product['cantidad'], 'id_responsible' => auth()->user()->id]);
                    }
                    if(isset($product['ubicaciones'])){
                        // dd($product['ubicaciones']);
                        $store_product = \App\Models\Product_Store::where('id_product', $nuevo_producto->id)->where('id_store', $store->id)->first();
                        if(isset($store_product)){
                            $store_product->positions()->delete();
                            foreach ($product['ubicaciones'] as $key => $position) {
                                \App\Models\Positions_product_store::create([
                                    'id_product_store' => $store_product->id,
                                    'position' => str_replace(' ', '', $position)
                                ]);
                            }
                        }
                    }
                }
                else{
                    dd('no esta entrando al if');
                }
            }
            \DB::commit();
        } catch (Exception $e) {
            dd('srieojas');
            \DB::rollback();
            return $e;
        }
        
    }

    // ************************************************************************************************
    // ************************************************************************************************

}