<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Support\OnlineExperimentLab;
use Illuminate\View\View;

class OnlineExperimentController extends Controller
{
    public function hub(): View
    {
        $labs = Experiment::query()
            ->with('category')
            ->published()
            ->where('online_lab_enabled', true)
            ->whereNotNull('online_lab_type')
            ->orderBy('online_lab_sort_order')
            ->orderByDesc('published_at')
            ->get()
            ->filter(fn (Experiment $exp) => OnlineExperimentLab::isPlayable($exp->online_lab_type));

        return view('frontend.experiments.online-hub', [
            'labs' => $labs,
            'labCount' => $labs->count(),
        ]);
    }

    public function play(Experiment $experiment): View
    {
        abort_unless($experiment->isPublished(), 404);
        abort_unless($experiment->online_lab_enabled && OnlineExperimentLab::isPlayable($experiment->online_lab_type), 404);

        return view('frontend.experiments.online-play', [
            'experiment' => $experiment,
            'labType' => $experiment->online_lab_type,
            'labTypeLabel' => OnlineExperimentLab::typeLabel($experiment->online_lab_type),
        ]);
    }
}
