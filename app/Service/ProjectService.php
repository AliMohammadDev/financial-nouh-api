<?php


namespace App\Service;

use App\Models\Project;
use App\Models\ProjectFund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class  ProjectService
{
  public function findAll(): Collection
  {
    return Project::with(['client.user', 'projectFunds.fund'])->get();
  }

  public function findOne(int $id): Project
  {
    return Project::with(['client.user', 'projectFunds.fund'])->findOrFail($id);
  }

  public function create(array $data): Project
  {
    return DB::transaction(function () use ($data) {
      return Project::create($data);
    });
  }

  public function update(int $id, array $data): Project
  {
    $Project = Project::findOrFail($id);

    DB::transaction(function () use ($Project, $data) {
      $Project->update($data);
    });

    return $Project->load(['client.user', 'projectFunds.fund']);
  }

  public function delete(int $id): bool
  {
    $Project = Project::findOrFail($id);

    return DB::transaction(function () use ($Project) {
      return (bool) $Project->delete();
    });
  }

  // project funds
  public function attachFund(int $projectId, array $data): ProjectFund
  {
    return DB::transaction(function () use ($projectId, $data) {
      return ProjectFund::firstOrCreate([
        'project_id' => $projectId,
        'fund_id'    => $data['fund_id']
      ]);
    });
  }

  public function detachFund(int $projectFundId): bool
  {
    $projectFund = ProjectFund::findOrFail($projectFundId);
    return DB::transaction(function () use ($projectFund) {
      return (bool) $projectFund->delete();
    });
  }
}
