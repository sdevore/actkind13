<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Databases carried over from the previous app kept their rows' ids but not their sequences, so new inserts
     * collide with existing primary keys. Point every column-owned sequence just past its table's highest value.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $ownedSequences = DB::select(<<<'SQL'
            select seq.relname as sequence_name, tbl.relname as table_name, col.attname as column_name
            from pg_class seq
            join pg_namespace ns on ns.oid = seq.relnamespace
            join pg_depend dep on dep.objid = seq.oid
                and dep.classid = 'pg_class'::regclass
                and dep.refclassid = 'pg_class'::regclass
                and dep.deptype in ('a', 'i')
            join pg_class tbl on tbl.oid = dep.refobjid
            join pg_attribute col on col.attrelid = tbl.oid and col.attnum = dep.refobjsubid
            where seq.relkind = 'S'
                and ns.nspname = current_schema()
            SQL);

        foreach ($ownedSequences as $ownedSequence) {
            DB::select(
                "select setval(?::regclass, coalesce(max(\"{$ownedSequence->column_name}\"), 0) + 1, false) from \"{$ownedSequence->table_name}\"",
                ["\"{$ownedSequence->sequence_name}\""],
            );
        }
    }
};
