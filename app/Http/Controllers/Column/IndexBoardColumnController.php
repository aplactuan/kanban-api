<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Concerns\ParsesIncludes;
use App\Http\Controllers\Controller;
use App\Http\Resources\ColumnResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexBoardColumnController extends Controller
{
    use ParsesIncludes;

    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(Request $request, int $board): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);

        $columns = $this->columnRepository->getAllForBoard($existingBoard);

        $relations = $this->parseIncludes($request, ['tasks']);

        if ($relations !== []) {
            $columns->load($relations);
        }

        return ColumnResource::collection($columns);
    }
}
