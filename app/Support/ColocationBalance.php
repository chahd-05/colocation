<?php

namespace App\Support;

use App\Models\Colocation;

class ColocationBalance
{
    public static function compute(Colocation $colocation): array
    {
        $memberships = $colocation->activeMemberships()->with('user')->get();
        $users = $memberships->pluck('user')->filter();

        if ($users->isEmpty()) {
            return [
                'members' => [],
                'total_expenses' => 0,
                'settlements' => [],
            ];
        }

        $userIds = $users->pluck('id');
        $expenses = $colocation->expenses()->whereIn('payer_id', $userIds)->get();
        $payments = $colocation->payments()->whereIn('from_user_id', $userIds)->whereIn('to_user_id', $userIds)->get();

        $totalExpenses = (float) $expenses->sum('amount');
        $share = $users->count() > 0 ? ($totalExpenses / $users->count()) : 0;

        $members = [];
        foreach ($users as $user) {
            $paid = (float) $expenses->where('payer_id', $user->id)->sum('amount');
            $sent = (float) $payments->where('from_user_id', $user->id)->sum('amount');
            $received = (float) $payments->where('to_user_id', $user->id)->sum('amount');

            $balance = round($paid + $received - $sent - $share, 2);

            $members[$user->id] = [
                'user' => $user,
                'paid' => round($paid, 2),
                'share' => round($share, 2),
                'sent' => round($sent, 2),
                'received' => round($received, 2),
                'balance' => $balance,
            ];
        }

        return [
            'members' => $members,
            'total_expenses' => round($totalExpenses, 2),
            'settlements' => self::buildSettlements($members),
        ];
    }

    public static function buildSettlements(array $members): array
    {
        $debtors = [];
        $creditors = [];
        $settlements = [];

        foreach ($members as $member) {
            if ($member['balance'] < 0) {
                $debtors[] = [
                    'user' => $member['user'],
                    'amount' => abs($member['balance']),
                ];
            } elseif ($member['balance'] > 0) {
                $creditors[] = [
                    'user' => $member['user'],
                    'amount' => $member['balance'],
                ];
            }
        }

        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $payAmount = min($debtors[$i]['amount'], $creditors[$j]['amount']);
            $payAmount = round($payAmount, 2);

            if ($payAmount > 0) {
                $settlements[] = [
                    'from_user' => $debtors[$i]['user'],
                    'to_user' => $creditors[$j]['user'],
                    'amount' => $payAmount,
                ];
            }

            $debtors[$i]['amount'] = round($debtors[$i]['amount'] - $payAmount, 2);
            $creditors[$j]['amount'] = round($creditors[$j]['amount'] - $payAmount, 2);

            if ($debtors[$i]['amount'] <= 0) {
                $i++;
            }
            if ($creditors[$j]['amount'] <= 0) {
                $j++;
            }
        }

        return $settlements;
    }
}
