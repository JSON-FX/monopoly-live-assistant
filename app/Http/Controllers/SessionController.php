<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionCreateRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SessionService $sessionService
    ) {}

    /**
     * Store a newly created session.
     *
     * @param SessionCreateRequest $request
     * @return JsonResponse
     */
    public function store(SessionCreateRequest $request): JsonResponse
    {
        try {
            $session = $this->sessionService->createSession($request->user());
            $sessionData = $this->sessionService->formatSessionResponse($session);

            return response()->json([
                'success' => true,
                'message' => 'Session created successfully',
                'data' => $sessionData,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create session',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
