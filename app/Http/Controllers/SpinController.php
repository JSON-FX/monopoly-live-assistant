<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpinCreateRequest;
use App\Services\SpinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SpinController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SpinService $spinService
    ) {}

    /**
     * Store a newly created spin.
     *
     * @param SpinCreateRequest $request
     * @param int $sessionId
     * @return JsonResponse
     */
    public function store(SpinCreateRequest $request, int $sessionId): JsonResponse
    {
        try {
            $updatedSession = $this->spinService->createSpin(
                $sessionId,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Spin added successfully',
                'data' => $updatedSession,
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
                'error' => 'The requested session does not exist or you do not have permission to access it.',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only add spins to your own sessions.',
            ], Response::HTTP_FORBIDDEN);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add spin',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 