<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatIdToFirmsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('firms', function (Blueprint $table) {
            $table->string('vat_id', 20)->nullable();
        });

        $sql = "SELECT firms.id, invoices.vat_id
                FROM payments JOIN jobs ON jobs.id = payments.job_id 
                JOIN firms ON firms.id = jobs.firm_id
                JOIN invoices ON invoices.id = payments.invoice_id
                WHERE invoices.vat_id != ''
                GROUP BY firms.id, invoices.vat_id";

        $result = $this->db->select($sql);

        foreach ($result as $row) {
            $this->db->unprepared("UPDATE firms SET vat_id = '$row->vat_id' WHERE id = $row->id");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('firms', function (Blueprint $table) {
            $table->dropColumn(['vat_id']);
        });
    }
}
