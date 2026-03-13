<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StoreBoardColumnController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(StoreBoardColumnRequest $request, int $board): JsonResponse
    {
        /** @var array{name: string, position?: int} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $column = $this->columnRepository->createForBoard($existingBoard, $validated);

        return (new ColumnResource($column))->response()->setStatusCode(201);
    }
}
