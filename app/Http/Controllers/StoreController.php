<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Repositories\StoreRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class StoreController extends AppBaseController
{
    /** @var StoreRepository $storeRepository*/
    private $storeRepository;

    public function __construct(StoreRepository $storeRepo)
    {
        $this->storeRepository = $storeRepo;
    }

    /**
     * Display a listing of the Store.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $stores = $this->storeRepository->all();

        return view('stores.index')
            ->with('stores', $stores);
    }

    /**
     * Show the form for creating a new Store.
     *
     * @return Response
     */
    public function create()
    {
        $products = \App\Models\Product::where('id_concession', auth()->user()->id_concession)->get();
        return view('stores.create')->with('products', $products)->with('stock_products', collect());
    }

    /**
     * Store a newly created Store in storage.
     *
     * @param CreateStoreRequest $request
     *
     * @return Response
     */
    public function store(CreateStoreRequest $request)
    {
        $input = $request->all();
        $store = \App\Models\Store::create([
            'name' => $input['name'],
            'address' => $input['name'],
            'id_concession' => auth()->user()->id_concession,
        ]);
        if(isset($input['products'])){
            foreach ($input['products'] as $id_product => $product) {
                if(isset($product['stock'])){
                    $store->products()->attach($id_product, ['stock' => $product['stock'], 'id_responsible' => auth()->user()->id]);
                    if(isset($product['positions']) && isset($product['stock'])){
                        $store_product = \App\Models\Product_Store::where('id_product', $id_product)->where('id_store', $store->id)->first();
                        if(isset($store_product)){
                            foreach ($product['positions'] as $key => $position) {
                                \App\Models\Positions_product_store::create([
                                    'id_product_store' => $store_product->id,
                                    'position' => $position
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        // $store = $this->storeRepository->create($input);

        Flash::success('Store saved successfully.');

        return redirect(route('stores.index'));
    }

    /**
     * Display the specified Store.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $store = $this->storeRepository->find($id);

        if (empty($store)) {
            Flash::error('Store not found');

            return redirect(route('stores.index'));
        }

        return view('stores.show')->with('store', $store);
    }

    /**
     * Show the form for editing the specified Store.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $store = $this->storeRepository->find($id);
        $products = \App\Models\Product::where('id_concession', auth()->user()->id_concession)->get();
        $stock_products = \App\Models\Product_Store::where('id_store', $id)->get();

        if (empty($store)) {
            Flash::error('Store not found');

            return redirect(route('stores.index'));
        }

        return view('stores.edit')->with('store', $store)->with('products', $products)->with('stock_products', $stock_products);
    }

    /**
     * Update the specified Store in storage.
     *
     * @param int $id
     * @param UpdateStoreRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateStoreRequest $request)
    {
        $input = $request->all();
        $store = $this->storeRepository->find($id);
        
        if (empty($store)) {
            Flash::error('Store not found');

            return redirect(route('stores.index'));
        }

        $store->name = $input['name'];
        $store->address = $input['address'];
        $store->save();
        // dd($input);

        // if(isset($input['products'])){
            $array_sync_products = [];
            foreach ($input['products'] as $id_product => $product) {
                if(isset($product['check'])){
                    // dd($product['stock']);
                    $array_sync_products[$id_product] = ['stock' => $product['stock'] ?? 0, 'id_responsible' => auth()->user()->id, 'position' => $product['position'] ?? null];
                }
            }

            // dd($array_sync_products);
            $store->products()->sync($array_sync_products);
            // dd($store->products);
        // }

        Flash::success('Store updated successfully.');

        return redirect(route('stores.index'));
    }

    /**
     * Remove the specified Store from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $store = $this->storeRepository->find($id);

        if (empty($store)) {
            Flash::error('Store not found');

            return redirect(route('stores.index'));
        }

        $this->storeRepository->delete($id);

        Flash::success('Store deleted successfully.');

        return redirect(route('stores.index'));
    }
}
