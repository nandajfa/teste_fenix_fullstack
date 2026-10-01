<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RankingRequest;
use App\Http\Resources\DashboardResource;
use App\Http\Resources\RankingResource;
use App\Services\DashboardService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(): DashboardResource
    {
        return new DashboardResource($this->dashboard->summary());
    }

    public function ranking(RankingRequest $request): AnonymousResourceCollection
    {
        return RankingResource::collection($this->dashboard->ranking(
            $request->validated('exam_id'),
            (int) $request->validated('page', 1),
            (int) $request->validated('per_page', 10),
        ));
    }
}
