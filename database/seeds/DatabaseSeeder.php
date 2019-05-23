<?php

use Carbon\Carbon;
use ElectronicInvoicing\Http\Logic\DraftJson;
use ElectronicInvoicing\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\{Role, Permission};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('voucher_types')->insert([
            ['code' => 1, 'name' => 'FACTURA', 'created_at' => Carbon::now()],
            ['code' => 4, 'name' => 'NOTA DE CRÉDITO', 'created_at' => Carbon::now()],
            ['code' => 5, 'name' => 'NOTA DE DÉBITO', 'created_at' => Carbon::now()],
            ['code' => 6, 'name' => 'GUÍA DE REMISIÓN', 'created_at' => Carbon::now()],
            ['code' => 7, 'name' => 'COMPROBANTE DE RETENCIÓN', 'created_at' => Carbon::now()],
            ['code' => 2, 'name' => 'NOTA O BOLETA DE VENTA', 'created_at' => Carbon::now()],
            ['code' => 3, 'name' => 'LIQUIDACIÓN DE COMPRA', 'created_at' => Carbon::now()],
            ['code' => 8, 'name' => 'BOLETO DE ESPECTÁCULO PÚBLICO', 'created_at' => Carbon::now()],
            ['code' => 9, 'name' => 'TIQUETE DE MÁQ. REGISTRADORA', 'created_at' => Carbon::now()]
        ]);

        DB::table('voucher_states')->insert([
            ['name' => 'DRAFT', 'created_at' => Carbon::now()],         // Voucher is stored in the JSON file, it might has empty or null values in some fields
            ['name' => 'SAVED', 'created_at' => Carbon::now()],         // Voucher is stored in the database with all values
            ['name' => 'ACCEPTED', 'created_at' => Carbon::now()],      // Same as the previous, but the voucher has been accepted by supervisor
            ['name' => 'REJECTED', 'created_at' => Carbon::now()],      // Same as the SAVED state, but the voucher has been rejected by supervisor
            ['name' => 'SENDED', 'created_at' => Carbon::now()],        // Same as the ACCEPTED state, but the XML file is created and signed and the voucher is sended to SRI and it does not have response
            ['name' => 'RECEIVED', 'created_at' => Carbon::now()],      // Same as the previous state, but the voucher has been validated by SRI
            ['name' => 'RETURNED', 'created_at' => Carbon::now()],      // Same as the SENDED state, but the voucher has been returned by SRI for bad structure
            ['name' => 'AUTHORIZED', 'created_at' => Carbon::now()],    // Same as the SENDED, but the voucher has been authorized by SRI
            ['name' => 'IN_PROCESS', 'created_at' => Carbon::now()],    // Same as the SENDED state, but the voucher has not been received a response by SRI
            ['name' => 'UNAUTHORIZED', 'created_at' => Carbon::now()],  // Same as the SENDED state, but the voucher has not been authorized by SRI
            ['name' => 'CANCELED', 'created_at' => Carbon::now()],      // Same as the AUTHORIZED state, but the voucher has been canceled in the system
            ['name' => 'CORRECTED', 'created_at' => Carbon::now()],      // Same as the SAVED state, but the voucher has been corrected after an unautorized, a returned or a rejected response
        ]);

        DB::table('environments')->insert([
            ['code' => 1, 'name' => 'PRUEBAS', 'created_at' => Carbon::now()],
            ['code' => 2, 'name' => 'PRODUCCIÓN', 'created_at' => Carbon::now()]
        ]);

        DB::table('identification_types')->insert([
            ['code' => 4, 'name' => 'RUC', 'created_at' => Carbon::now()],
            ['code' => 5, 'name' => 'CÉDULA', 'created_at' => Carbon::now()],
            ['code' => 6, 'name' => 'PASAPORTE', 'created_at' => Carbon::now()],
            ['code' => 7, 'name' => 'VENTA A CONSUMIDOR FINAL', 'created_at' => Carbon::now()],
            ['code' => 8, 'name' => 'IDENTIFICACIÓN DEL EXTERIOR', 'created_at' => Carbon::now()],
            ['code' => 9, 'name' => 'PLACA', 'created_at' => Carbon::now()]
        ]);

        DB::table('customers')->insert([
            ['identification_type_id' => 4, 'identification' => '9999999999999', 'social_reason' => 'CONSUMIDOR FINAL', 'created_at' => Carbon::now()]
        ]);

        DB::table('iva_taxes')->insert([
            ['code' => 2, 'auxiliary_code' => 0, 'description' => '0%', 'rate' => 0.00, 'created_at' => Carbon::now()],
            ['code' => 2, 'auxiliary_code' => 2, 'description' => '12%', 'rate' => 12.00, 'created_at' => Carbon::now()],
            ['code' => 2, 'auxiliary_code' => 3, 'description' => '14%', 'rate' => 14.00, 'created_at' => Carbon::now()],
            ['code' => 2, 'auxiliary_code' => 6, 'description' => 'NO OBJETO DE IMPUESTO', 'rate' => 0.00, 'created_at' => Carbon::now()],
            ['code' => 2, 'auxiliary_code' => 7, 'description' => 'EXENTO DE IVA', 'rate' => 0.00, 'created_at' => Carbon::now()]
        ]);

        DB::table('ice_taxes')->insert([
            ['code' => 3, 'auxiliary_code' => 3023, 'description' => 'Productos del tabaco y sucedáneos del tabaco (abarcan los productos preparados totalmente o en parte utilizando como materia prima hojas de tabaco y destinados a ser fumados, chupados, inhalados, mascados o utilizados como rapé).', 'specific_rate' => NULL, 'ad_valorem_rate' => 150.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3610, 'description' => 'Perfumes y aguas de tocador', 'specific_rate' => NULL, 'ad_valorem_rate' => 20.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3620, 'description' => 'Videojuegos', 'specific_rate' => NULL, 'ad_valorem_rate' => 35.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3630, 'description' => 'Armas de fuego, armas deportivas y municiones excepto aquellas adquiridas por la fuerza pública', 'specific_rate' => NULL, 'ad_valorem_rate' => 300.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3640, 'description' => 'Focos incandescentes excepto aquellos utilizados como insumos Automotrices. Cocinas, cocinetas, calefones y sistemas de calentamiento de agua, de uso domestico, que funcionen total o parcialmente mediante la combustión de gas.', 'specific_rate' => NULL, 'ad_valorem_rate' => 100.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3073, 'description' => 'Vehículos motorizados cuyo precio de venta al público sea de hasta USD 20.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 5.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3072, 'description' => 'Camionetas, furgonetas, camiones, y vehículos de rescate cuyo precio de venta al público sea de hasta USD 30.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 5.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3074, 'description' => 'Vehículos motorizados, excepto camionetas, furgonetas, camiones y vehículos de rescate, cuyo precio de venta al público sea superior a USD 20.000 y de hasta USD 30.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 10.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3075, 'description' => 'Vehículos motorizados, cuyo precio de venta al público sea superior a USD 30.000 y de hasta USD 40.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 15.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3077, 'description' => 'Vehículos motorizados, cuyo precio de venta al público sea superior a USD 40.000 y de hasta USD 50.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 20.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3078, 'description' => 'Vehículos motorizados cuyo precio de venta al público sea superior a USD 50.000 y de hasta USD 60.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 25.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3079, 'description' => 'Vehículos motorizados cuyo precio de venta al público sea superior a USD 60.000 y de hasta USD 70.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 30.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3080, 'description' => 'Vehículos motorizados cuyo precio de venta al público sea superior a USD 70.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 35.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3171, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea de hasta USD 35.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 2.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3172, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea superior a USD 35.000 y de hasta USD 40.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 8.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3173, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea superior a USD 40.000 y de hasta USD 50.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 14.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3174, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea superior a USD 50.000 y de hasta USD 60.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 20.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3175, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea superior a USD 60.000 y de hasta USD 70.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 26.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3176, 'description' => 'Vehículos híbridos o eléctricos cuyo precio de venta al público sea superior a USD 70.000', 'specific_rate' => NULL, 'ad_valorem_rate' => 32.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3081, 'description' => '3. Aviones, avionetas y helicópteros excepto aquellas destinadas al transporte comercial de pasajeros, carga y servicios; motos acuáticas, tricares, cuadrones, yates y barcos de recreo:', 'specific_rate' => NULL, 'ad_valorem_rate' => 15.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3092, 'description' => 'Servicios de televisión pagada', 'specific_rate' => NULL, 'ad_valorem_rate' => 15.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3650, 'description' => 'Servicios de casinos, salas de juego (bingo - mecánicos) y otros juegos de azar', 'specific_rate' => NULL, 'ad_valorem_rate' => 35.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3093, 'description' => 'Servicio de telefonía fija y planes que comercialicen únicamente voz, datos y sms del servicio móvil avanzado prestado a sociedades', 'specific_rate' => NULL, 'ad_valorem_rate' => 15.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3660, 'description' => 'Las cuotas, membresías, afiliaciones, acciones y similares que cobren a sus miembros y usuarios los Clubes Sociales, para prestar sus servicios, cuyo monto en su conjunto supere los US $ 1.500 anuales', 'specific_rate' => NULL, 'ad_valorem_rate' => 35.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3011, 'description' => 'Cigarrillos', 'specific_rate' => 0.16, 'ad_valorem_rate' => NULL, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3031, 'description' => 'Bebidas alcohólicas, incluida la cerveza artesanal', 'specific_rate' => 7.22, 'ad_valorem_rate' => 75.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3041, 'description' => 'Cerveza Industrial', 'specific_rate' => 12.00, 'ad_valorem_rate' => 75.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3054, 'description' => 'Bebidas gaseosas con contenido de azúcar menor o igual a 25 gramos por litro de bebida. Bebidas energizantes.', 'specific_rate' => NULL, 'ad_valorem_rate' => 10.00, 'created_at' => Carbon::now()],
            ['code' => 3, 'auxiliary_code' => 3111, 'description' => 'Bebidas no alcohólicas y gaseosas con contenido de azúcar mayor a 25 gramos por litro de bebida , excepto energizantes', 'specific_rate' => 0.18, 'ad_valorem_rate' => NULL, 'created_at' => Carbon::now()]
        ]);

        DB::table('irbpnr_taxes')->insert([
            ['code' => 5, 'auxiliary_code' => 5001, 'description' => 'Botellas plásticas no retornables', 'specific_rate' => 0.02, 'created_at' => Carbon::now()]
        ]);

        DB::table('currencies')->insert([
            ['id' => 1, 'symbol' => '$', 'iso' => 'USD', 'name' => 'DÓLAR', 'created_at' => Carbon::now()]
        ]);

        DB::table('time_units')->insert([
            ['id' => 1, 'name' => 'NINGUNO', 'created_at' => Carbon::now()],
            ['id' => 2, 'name' => 'DÍAS', 'created_at' => Carbon::now()],
            ['id' => 3, 'name' => 'MESES', 'created_at' => Carbon::now()],
            ['id' => 4, 'name' => 'AÑOS', 'created_at' => Carbon::now()]
        ]);

        DB::table('payment_methods')->insert([
            ['code' => 1, 'name' => 'SIN UTILIZACIÓN DEL SISTEMA FINANCIERO', 'created_at' => Carbon::now()],
            ['code' => 15, 'name' => 'COMPENSACIÓN DE DEUDAS', 'created_at' => Carbon::now()],
            ['code' => 16, 'name' => 'TARJETA DE DÉBITO', 'created_at' => Carbon::now()],
            ['code' => 17, 'name' => 'DINERO ELECTRÓNICO', 'created_at' => Carbon::now()],
            ['code' => 18, 'name' => 'TARJETA PREPAGO', 'created_at' => Carbon::now()],
            ['code' => 19, 'name' => 'TARJETA DE CRÉDITO', 'created_at' => Carbon::now()],
            ['code' => 20, 'name' => 'OTROS CON UTILIZACIÓN DEL SISTEMA FINANCIERO', 'created_at' => Carbon::now()],
            ['code' => 21, 'name' => 'ENDOSO DE TÍTULOS', 'created_at' => Carbon::now()]
        ]);

        DB::table('retention_taxes')->insert([
            ['code' => 1, 'tax' => 'RENTA', 'created_at' => Carbon::now()],
            ['code' => 2, 'tax' => 'IVA', 'created_at' => Carbon::now()],
            ['code' => 6, 'tax' => 'ISD', 'created_at' => Carbon::now()]
        ]);

        DB::table('retention_tax_descriptions')->insert([
            ['retention_tax_id' => 2, 'code' => '9', 'description' => '10%', 'rate' => 10.0, 'min_rate' => 10.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '10', 'description' => '20%', 'rate' => 20.0, 'min_rate' => 20.0, 'max_rate' => 20.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '1', 'description' => '30%', 'rate' => 30.0, 'min_rate' => 30.0, 'max_rate' => 30.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '11', 'description' => '50%', 'rate' => 50.0, 'min_rate' => 50.0, 'max_rate' => 50.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '2', 'description' => '70%', 'rate' => 70.0, 'min_rate' => 70.0, 'max_rate' => 70.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '3', 'description' => '100%', 'rate' => 100.0, 'min_rate' => 100.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '7', 'description' => 'RETENCIÓN EN CERO (0%)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 2, 'code' => '8', 'description' => 'NO PROCEDE RETENCIÓN (0%)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 3, 'code' => '4580', 'description' => '5%', 'rate' => 5.0, 'min_rate' => 5.0, 'max_rate' => 5.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '303', 'description' => 'Honorarios profesionales y demás pagos por servicios relacionados con el título profesional', 'rate' => 10.0, 'min_rate' => 10.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304', 'description' => 'Servicios predomina el intelecto no relacionados con el título profesional', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304A', 'description' => 'Comisiones y demás pagos por servicios predomina intelecto no relacionados con el título profesional', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304B', 'description' => 'Pagos a notarios y registradores de la propiedad y mercantil por sus actividades ejercidas como tales', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304C', 'description' => 'Pagos a deportistas, entrenadores, árbitros, miembros del cuerpo técnico por sus actividades ejercidas como tales', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304D', 'description' => 'Pagos a artistas por sus actividades ejercidas como tales', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '304E', 'description' => 'Honorarios y demás pagos por servicios de docencia', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '307', 'description' => 'Servicios predomina la mano de obra', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '308', 'description' => 'Utilización o aprovechamiento de la imagen o renombre', 'rate' => 10.0, 'min_rate' => 10.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '309', 'description' => 'Servicios prestados por medios de comunicación y agencias de publicidad', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '310', 'description' => 'Servicio de transporte privado de pasajeros o transporte público o privado de carga', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '311', 'description' => 'Pagos a través de liquidación de compra (nivel cultural o rusticidad)', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '312', 'description' => 'Transferencia de bienes muebles de naturaleza corporal', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '312A', 'description' => 'Compra de bienes de origen agrícola, avícola, pecuario, apícola, cunícula, bioacuático, y forestal', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '312B', 'description' => 'Impuesto a la Renta único para la actividad de producción y cultivo de palma aceitera', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '314A', 'description' => 'Regalías por concepto de franquicias de acuerdo a Ley de Propiedad Intelectual - pago a personas naturales', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '314B', 'description' => 'Cánones, derechos de autor,  marcas, patentes y similares de acuerdo a Ley de Propiedad Intelectual – pago a personas naturales', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '314C', 'description' => 'Regalías por concepto de franquicias de acuerdo a Ley de Propiedad Intelectual  - pago a sociedades', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '314D', 'description' => 'Cánones, derechos de autor,  marcas, patentes y similares de acuerdo a Ley de Propiedad Intelectual – pago a sociedades', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '319', 'description' => 'Cuotas de arrendamiento mercantil (prestado por sociedades), inclusive la de opción de compra', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '320', 'description' => 'Arrendamiento bienes inmuebles', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '322', 'description' => 'Seguros y reaseguros (primas y cesiones)', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323', 'description' => 'Rendimientos financieros pagados a naturales y sociedades  (No a IFIs)', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323A', 'description' => 'Rendimientos financieros: depósitos Cta. Corriente', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323B1', 'description' => 'Rendimientos financieros:  depósitos Cta. Ahorros Sociedades', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323E', 'description' => 'Rendimientos financieros: depósito a plazo fijo  gravados', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323E2', 'description' => 'Rendimientos financieros: depósito a plazo fijo exentos', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323F', 'description' => 'Rendimientos financieros: operaciones de reporto - repos', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323G', 'description' => 'Inversiones (captaciones) rendimientos distintos de aquellos pagados a IFIs', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323H', 'description' => 'Rendimientos financieros: obligaciones', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323I', 'description' => 'Rendimientos financieros: bonos convertible en acciones', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323 M', 'description' => 'Rendimientos financieros: Inversiones en títulos valores en renta fija gravados ', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323 N', 'description' => 'Rendimientos financieros: Inversiones en títulos valores en renta fija exentos', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323 O', 'description' => 'Intereses y demás rendimientos financieros pagados a bancos y otras entidades sometidas al control de la Superintendencia de Bancos y de la Economía Popular y Solidaria', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323 P', 'description' => 'Intereses pagados por entidades del sector público a favor de sujetos pasivos', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323Q', 'description' => 'Otros intereses y rendimientos financieros gravados ', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323R', 'description' => 'Otros intereses y rendimientos financieros exentos', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323S', 'description' => 'Pagos y créditos en cuenta efectuados por el BCE y los depósitos centralizados de valores, en calidad de intermediarios, a instituciones del sistema financiero por cuenta de otras personas naturales y sociedades', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323T', 'description' => 'Rendimientos financieros originados en la deuda pública ecuatoriana', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '323U', 'description' => 'Rendimientos financieros originados en títulos valores de obligaciones de 360 días o más para el financiamiento de proyectos públicos en asociación público-privada', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '324A', 'description' => 'Intereses y comisiones en operaciones de crédito entre instituciones del sistema financiero y entidades economía popular y solidaria.', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '324B', 'description' => 'Inversiones entre instituciones del sistema financiero y entidades economía popular y solidaria', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '324C', 'description' => 'Pagos y créditos en cuenta efectuados por el BCE y los depósitos centralizados de valores, en calidad de intermediarios, a instituciones del sistema financiero por cuenta de otras instituciones del sistema financiero', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '325', 'description' => 'Anticipo dividendos a residentes o establecidos en el Ecuador', 'rate' => 22.0, 'min_rate' => 22.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '325A', 'description' => 'Préstamos accionistas, beneficiarios o partícipes residentes o establecidos en el Ecuador', 'rate' => 22.0, 'min_rate' => 22.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '326', 'description' => 'Dividendos distribuidos que correspondan al impuesto a la renta único establecido en el art. 27 de la LRTI', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '327', 'description' => 'Dividendos distribuidos a personas naturales residentes', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '328', 'description' => 'Dividendos distribuidos a sociedades residentes', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '329', 'description' => 'Dividendos distribuidos a fideicomisos residentes', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '330', 'description' => 'Dividendos gravados distribuidos en acciones (reinversión de utilidades sin derecho a reducción tarifa IR)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '331', 'description' => 'Dividendos exentos distribuidos en acciones (reinversión de utilidades con derecho a reducción tarifa IR)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332', 'description' => 'Otras compras de bienes y servicios no sujetas a retención', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332B', 'description' => 'Compra de bienes inmuebles', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332C', 'description' => 'Transporte público de pasajeros', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332D', 'description' => 'Pagos en el país por transporte de pasajeros o transporte internacional de carga, a compañías nacionales o extranjeras de aviación o marítimas', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332E', 'description' => 'Valores entregados por las cooperativas de transporte a sus socios', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332F', 'description' => 'Compraventa de divisas distintas al dólar de los Estados Unidos de América', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332G', 'description' => 'Pagos con tarjeta de crédito', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332H', 'description' => 'Pago al exterior tarjeta de crédito reportada por la Emisora de tarjeta de crédito, solo RECAP', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '332I', 'description' => 'Pago a través de convenio de debito (Clientes IFI`s)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '333', 'description' => 'Enajenación de derechos representativos de capital y otros derechos cotizados en bolsa ecuatoriana', 'rate' => 0.2, 'min_rate' => 0.2, 'max_rate' => 0.2, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '334', 'description' => 'Enajenación de derechos representativos de capital y otros derechos no cotizados en bolsa ecuatoriana', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '335', 'description' => 'Loterías, rifas, apuestas y similares', 'rate' => 15.0, 'min_rate' => 15.0, 'max_rate' => 15.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '336', 'description' => 'Venta de combustibles a comercializadoras', 'rate' => 0.2, 'min_rate' => 0.2, 'max_rate' => 0.2, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '337', 'description' => 'Venta de combustibles a distribuidores', 'rate' => 0.3, 'min_rate' => 0.3, 'max_rate' => 0.3, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '338', 'description' => 'Compra local de banano a productor', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '339', 'description' => 'Liquidación impuesto único a la venta local de banano de producción propia', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '340', 'description' => 'Impuesto único a la exportación de banano de producción propia - componente 1', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '341', 'description' => 'Impuesto único a la exportación de banano de producción propia - componente 2', 'rate' => 1.25, 'min_rate' => 1.25, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '342', 'description' => 'Impuesto único a la exportación de banano producido por terceros', 'rate' => 0.5, 'min_rate' => 0.5, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '343', 'description' => 'Otras retenciones aplicables el 1%', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '343A', 'description' => 'Energía eléctrica', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '343B', 'description' => 'Actividades de construcción de obra material inmueble, urbanización, lotización o actividades similares', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '343C', 'description' => 'Impuesto Redimible a las botellas plásticas - IRBP', 'rate' => 1.0, 'min_rate' => 1.0, 'max_rate' => 1.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '344', 'description' => 'Otras retenciones aplicables el 2%', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '344A', 'description' => 'Pago local tarjeta de crédito reportada por la Emisora de tarjeta de crédito, solo RECAP', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '344B', 'description' => 'Adquisición de sustancias minerales dentro del territorio nacional', 'rate' => 2.0, 'min_rate' => 2.0, 'max_rate' => 2.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '345', 'description' => 'Otras retenciones aplicables el 8%', 'rate' => 8.0, 'min_rate' => 8.0, 'max_rate' => 8.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '346', 'description' => 'Otras retenciones aplicables a otros porcentajes', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '346A', 'description' => 'Otras ganancias de capital distintas de enajenación de derechos representativos de capital', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '346B', 'description' => 'Donaciones en dinero -Impuesto a la donaciones', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '346C', 'description' => 'Retención a cargo del propio sujeto pasivo por la exportación de concentrados y/o elementos metálicos', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '346D', 'description' => 'Retención a cargo del propio sujeto pasivo por la comercialización de productos forestales', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '500', 'description' => 'Pago a no residentes - Rentas Inmobiliarias', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '501', 'description' => 'Pago a no residentes - Beneficios/Servicios  Empresariales', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '501A', 'description' => 'Pago a no residentes - Servicios técnicos, administrativos o de consultoría y regalías', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '503', 'description' => 'Pago a no residentes- Navegación Marítima y/o aérea', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504', 'description' => 'Pago a no residentes- Dividendos distribuidos a personas naturales (domicilados o no en paraiso fiscal) o a sociedades sin beneficiario efectivo persona natural residente en Ecuador (ni domiciladas en paraíso fiscal)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504A', 'description' => 'Pago al exterior - Dividendos a sociedades con beneficiario efectivo persona natural residente en el Ecuador (no domiciliada en paraísos fiscales o regímenes de menor imposición)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504B', 'description' => 'Pago a no residentes - Dividendos a fideicomisos con beneficiario efectivo persona natural residente en el Ecuador (no domiciliada en paraísos fiscales o regímenes de menor imposición)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504C', 'description' => 'Pago a no residentes - Dividendos a sociedades domiciladas en paraísos fiscales o regímenes de menor imposición (con o sin beneficiario efectivo persona natural residente en el Ecuador)', 'rate' => 10.0, 'min_rate' => 10.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504D', 'description' => 'Pago a no residentes - Dividendos a fideicomisos domiciladas en paraísos fiscales o regímenes de menor imposición (con o sin beneficiario efectivo persona natural residente en el Ecuador)', 'rate' => 10.0, 'min_rate' => 10.0, 'max_rate' => 10.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504E', 'description' => 'Pago a no residentes - Anticipo dividendos (no domiciliada en paraísos fiscales o regímenes de menor imposición)', 'rate' => 22.0, 'min_rate' => 22.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504F', 'description' => 'Pago a no residentes - Anticipo dividendos (domiciliadas en paraísos fiscales o regímenes de menor imposición)', 'rate' => 28.0, 'min_rate' => 28.0, 'max_rate' => 28.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504G', 'description' => 'Pago a no residentes - Préstamos accionistas, beneficiarios o partìcipes (no domiciladas en paraísos fiscales o regímenes de menor imposición)', 'rate' => 22.0, 'min_rate' => 22.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504H', 'description' => 'Pago a no residentes - Préstamos accionistas, beneficiarios o partìcipes (domiciladas en paraísos fiscales o regímenes de menor imposición)', 'rate' => 28.0, 'min_rate' => 28.0, 'max_rate' => 28.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504I', 'description' => 'Pago a no residentes - Préstamos no comerciales a partes relacionadas  (no domiciladas en paraísos fiscales o regímenes de menor imposición)', 'rate' => 22.0, 'min_rate' => 22.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '504J', 'description' => 'Pago a no residentes - Préstamos no comerciales a partes relacionadas  (domiciladas en paraísos fiscales o regímenes de menor imposición)', 'rate' => 28.0, 'min_rate' => 28.0, 'max_rate' => 28.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505', 'description' => 'Pago a no residentes - Rendimientos financieros', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505A', 'description' => 'Pago a no residentes – Intereses de créditos de Instituciones Financieras del exterior', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505B', 'description' => 'Pago a no residentes – Intereses de créditos de gobierno a gobierno', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505C', 'description' => 'Pago a no residentes – Intereses de créditos de organismos multilaterales', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505D', 'description' => 'Pago a no residentes - Intereses por financiamiento de proveedores externos', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505E', 'description' => 'Pago a no residentes - Intereses de otros créditos externos', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 25.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '505F', 'description' => 'Pago a no residentes - Otros Intereses y Rendimientos Financieros', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '509', 'description' => 'Pago a no residentes- Cánones, derechos de autor,  marcas, patentes y similares', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '509A', 'description' => 'PPago a no residentes - Regalías por concepto de franquicias', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '510', 'description' => 'Pago a no residentes - Otras ganancias de capital distintas de enajenación de derechos representativos de capital', 'rate' => 5.0, 'min_rate' => 5.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '511', 'description' => 'Pago a no residentes - Servicios profesionales independientes', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '512', 'description' => 'Pago a no residentes - Servicios profesionales dependientes', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '513', 'description' => 'Pago a no residentes- Artistas', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '513A', 'description' => 'Pago a no residentes - Deportistas', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '514', 'description' => 'Pago a no residentes - Participación de consejeros', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '515', 'description' => 'Pago a no residentes - Entretenimiento Público', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '516', 'description' => 'Pago a no residentes - Pensiones', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '517', 'description' => 'Pago a no residentes- Reembolso de Gastos', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '518', 'description' => 'Pago a no residentes- Funciones Públicas', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '519', 'description' => 'Pago a no residentes - Estudiantes', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520A', 'description' => 'Pago a no residentes - Pago a proveedores de servicios hoteleros y turísticos en el exterior', 'rate' => 25.0, 'min_rate' => 25.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520B', 'description' => 'Pago a no residentes - Arrendamientos mercantil internacional', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520D', 'description' => 'Pago a no residentes - Comisiones por exportaciones y por promoción de turismo receptivo', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520E', 'description' => 'Pago a no residentes - Por las empresas de transporte marítimo o aéreo y por empresas pesqueras de alta mar, por su actividad.', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 0.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520F', 'description' => 'Pago a no residentes - Por las agencias internacionales de prensa', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '520G', 'description' => 'Pago a no residentes - Contratos de fletamento de naves para empresas de transporte aéreo o marítimo internacional', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '521', 'description' => 'Pago a no residentes - Enajenación de derechos representativos de capital y otros derechos', 'rate' => 5.0, 'min_rate' => 5.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '523A', 'description' => 'Pago a no residentes - Seguros y reaseguros (primas y cesiones)', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 35.0, 'created_at' => Carbon::now()],
            ['retention_tax_id' => 1, 'code' => '525', 'description' => 'Pago a no residentes- Donaciones en dinero -Impuesto a la donaciones', 'rate' => 0.0, 'min_rate' => 0.0, 'max_rate' => 100.0, 'created_at' => Carbon::now()]
        ]);

        DB::table('quotas')->insert([
            ['description' => 'BASIC', 'max_users_owner' => 1, 'max_users_supervisor' => 1, 'max_users_employee' => NULL, 'max_branches' => 1, 'max_emission_points'=> 3 , 'created_at'=>Carbon::now()],
            ['description' => 'GOLD', 'max_users_owner' => 1, 'max_users_supervisor' => 5, 'max_users_employee' => 25, 'max_branches' => 3, 'max_emission_points'=> 15 , 'created_at'=>Carbon::now()],
            ['description' => 'PLATINUM', 'max_users_owner' => 1, 'max_users_supervisor' => 10, 'max_users_employee' => 50, 'max_branches' => 5, 'max_emission_points'=> 30, 'created_at' =>Carbon::now()],
        ]);

        $role_admin = Role::create(['name' => 'admin']);
        $role_api = Role::create(['name' => 'api']);
        $role_owner = Role::create(['name' => 'owner']);
        $role_supervisor = Role::create(['name' => 'supervisor']);
        $role_employee = Role::create(['name' => 'employee']);
        $role_customer = Role::create(['name' => 'customer']);

        $permission_create_companies = Permission::create(['name' => 'create_companies']);
        $permission_read_companies = Permission::create(['name' => 'read_companies']);
        $permission_update_companies = Permission::create(['name' => 'update_companies']);
        $permission_delete_soft_companies = Permission::create(['name' => 'delete_soft_companies']);
        $permission_delete_hard_companies = Permission::create(['name' => 'delete_hard_companies']);
        $permission_create_branches = Permission::create(['name' => 'create_branches']);
        $permission_read_branches = Permission::create(['name' => 'read_branches']);
        $permission_update_branches = Permission::create(['name' => 'update_branches']);
        $permission_delete_soft_branches = Permission::create(['name' => 'delete_soft_branches']);
        $permission_delete_hard_branches = Permission::create(['name' => 'delete_hard_branches']);
        $permission_create_emission_points = Permission::create(['name' => 'create_emission_points']);
        $permission_read_emission_points = Permission::create(['name' => 'read_emission_points']);
        $permission_update_emission_points = Permission::create(['name' => 'update_emission_points']);
        $permission_delete_soft_emission_points = Permission::create(['name' => 'delete_soft_emission_points']);
        $permission_delete_hard_emission_points = Permission::create(['name' => 'delete_hard_emission_points']);
        $permission_create_customers = Permission::create(['name' => 'create_customers']);
        $permission_read_customers = Permission::create(['name' => 'read_customers']);
        $permission_update_customers = Permission::create(['name' => 'update_customers']);
        $permission_delete_soft_customers = Permission::create(['name' => 'delete_soft_customers']);
        $permission_delete_hard_customers = Permission::create(['name' => 'delete_hard_customers']);
        $permission_create_users = Permission::create(['name' => 'create_users']);
        $permission_read_users = Permission::create(['name' => 'read_users']);
        $permission_update_users = Permission::create(['name' => 'update_users']);
        $permission_delete_soft_users = Permission::create(['name' => 'delete_soft_users']);
        $permission_delete_hard_users = Permission::create(['name' => 'delete_hard_users']);
        $permission_create_products = Permission::create(['name' => 'create_products']);
        $permission_read_products = Permission::create(['name' => 'read_products']);
        $permission_update_products = Permission::create(['name' => 'update_products']);
        $permission_delete_soft_products = Permission::create(['name' => 'delete_soft_products']);
        $permission_delete_hard_products = Permission::create(['name' => 'delete_hard_products']);
        $permission_create_vouchers = Permission::create(['name' => 'create_vouchers']);
        $permission_read_vouchers = Permission::create(['name' => 'read_vouchers']);
        $permission_update_vouchers = Permission::create(['name' => 'update_vouchers']);
        $permission_delete_vouchers = Permission::create(['name' => 'delete_vouchers']);
        $permission_send_vouchers = Permission::create(['name' => 'send_vouchers']);
        $permission_report_vouchers = Permission::create(['name' => 'report_vouchers']);

        $role_admin->givePermissionTo($permission_create_companies);
        $role_admin->givePermissionTo($permission_read_companies);
        $role_admin->givePermissionTo($permission_update_companies);
        $role_admin->givePermissionTo($permission_delete_soft_companies);
        $role_admin->givePermissionTo($permission_delete_hard_companies);
        $role_owner->givePermissionTo($permission_read_companies);
        $role_owner->givePermissionTo($permission_update_companies);

        $role_admin->givePermissionTo($permission_create_branches);
        $role_admin->givePermissionTo($permission_read_branches);
        $role_admin->givePermissionTo($permission_update_branches);
        $role_admin->givePermissionTo($permission_delete_soft_branches);
        $role_admin->givePermissionTo($permission_delete_hard_branches);
        $role_owner->givePermissionTo($permission_create_branches);
        $role_owner->givePermissionTo($permission_read_branches);
        $role_owner->givePermissionTo($permission_update_branches);
        $role_owner->givePermissionTo($permission_delete_soft_branches);
        $role_owner->givePermissionTo($permission_delete_hard_branches);

        $role_admin->givePermissionTo($permission_create_emission_points);
        $role_admin->givePermissionTo($permission_read_emission_points);
        $role_admin->givePermissionTo($permission_update_emission_points);
        $role_admin->givePermissionTo($permission_delete_soft_emission_points);
        $role_admin->givePermissionTo($permission_delete_hard_emission_points);
        $role_owner->givePermissionTo($permission_create_emission_points);
        $role_owner->givePermissionTo($permission_read_emission_points);
        $role_owner->givePermissionTo($permission_update_emission_points);
        $role_owner->givePermissionTo($permission_delete_soft_emission_points);
        $role_owner->givePermissionTo($permission_delete_hard_emission_points);

        $role_admin->givePermissionTo($permission_create_customers);
        $role_admin->givePermissionTo($permission_read_customers);
        $role_admin->givePermissionTo($permission_update_customers);
        $role_admin->givePermissionTo($permission_delete_soft_customers);
        $role_admin->givePermissionTo($permission_delete_hard_customers);
        $role_api->givePermissionTo($permission_create_customers);
        $role_api->givePermissionTo($permission_read_customers);
        $role_api->givePermissionTo($permission_update_customers);
        $role_api->givePermissionTo($permission_delete_soft_customers);
        $role_api->givePermissionTo($permission_delete_hard_customers);
        $role_owner->givePermissionTo($permission_create_customers);
        $role_owner->givePermissionTo($permission_read_customers);
        $role_owner->givePermissionTo($permission_update_customers);
        $role_owner->givePermissionTo($permission_delete_soft_customers);
        $role_owner->givePermissionTo($permission_delete_hard_customers);
        $role_supervisor->givePermissionTo($permission_create_customers);
        $role_supervisor->givePermissionTo($permission_read_customers);
        $role_supervisor->givePermissionTo($permission_update_customers);
        $role_supervisor->givePermissionTo($permission_delete_soft_customers);
        $role_employee->givePermissionTo($permission_create_customers);
        $role_employee->givePermissionTo($permission_read_customers);
        $role_employee->givePermissionTo($permission_update_customers);

        $role_admin->givePermissionTo($permission_create_users);
        $role_admin->givePermissionTo($permission_read_users);
        $role_admin->givePermissionTo($permission_update_users);
        $role_admin->givePermissionTo($permission_delete_soft_users);
        $role_admin->givePermissionTo($permission_delete_hard_users);
        $role_owner->givePermissionTo($permission_create_users);
        $role_owner->givePermissionTo($permission_read_users);
        $role_owner->givePermissionTo($permission_update_users);
        $role_owner->givePermissionTo($permission_delete_soft_users);
        $role_owner->givePermissionTo($permission_delete_hard_users);
        $role_supervisor->givePermissionTo($permission_create_users);
        $role_supervisor->givePermissionTo($permission_read_users);
        $role_supervisor->givePermissionTo($permission_update_users);
        $role_supervisor->givePermissionTo($permission_delete_soft_users);

        $role_admin->givePermissionTo($permission_create_products);
        $role_admin->givePermissionTo($permission_read_products);
        $role_admin->givePermissionTo($permission_update_products);
        $role_admin->givePermissionTo($permission_delete_soft_products);
        $role_admin->givePermissionTo($permission_delete_hard_products);
        $role_api->givePermissionTo($permission_create_products);
        $role_api->givePermissionTo($permission_read_products);
        $role_api->givePermissionTo($permission_update_products);
        $role_api->givePermissionTo($permission_delete_soft_products);
        $role_api->givePermissionTo($permission_delete_hard_products);
        $role_owner->givePermissionTo($permission_create_products);
        $role_owner->givePermissionTo($permission_read_products);
        $role_owner->givePermissionTo($permission_update_products);
        $role_owner->givePermissionTo($permission_delete_soft_products);
        $role_owner->givePermissionTo($permission_delete_hard_products);
        $role_supervisor->givePermissionTo($permission_create_products);
        $role_supervisor->givePermissionTo($permission_read_products);
        $role_supervisor->givePermissionTo($permission_update_products);
        $role_supervisor->givePermissionTo($permission_delete_soft_products);
        $role_employee->givePermissionTo($permission_create_products);
        $role_employee->givePermissionTo($permission_read_products);
        $role_employee->givePermissionTo($permission_update_products);

        $role_admin->givePermissionTo($permission_create_vouchers);
        $role_admin->givePermissionTo($permission_read_vouchers);
        $role_admin->givePermissionTo($permission_update_vouchers);
        $role_admin->givePermissionTo($permission_delete_vouchers);
        $role_api->givePermissionTo($permission_create_vouchers);
        $role_api->givePermissionTo($permission_read_vouchers);
        $role_api->givePermissionTo($permission_update_vouchers);
        $role_api->givePermissionTo($permission_delete_vouchers);
        $role_owner->givePermissionTo($permission_create_vouchers);
        $role_owner->givePermissionTo($permission_read_vouchers);
        $role_owner->givePermissionTo($permission_update_vouchers);
        $role_owner->givePermissionTo($permission_delete_vouchers);
        $role_supervisor->givePermissionTo($permission_create_vouchers);
        $role_supervisor->givePermissionTo($permission_read_vouchers);
        $role_supervisor->givePermissionTo($permission_update_vouchers);
        $role_supervisor->givePermissionTo($permission_delete_vouchers);
        $role_employee->givePermissionTo($permission_create_vouchers);
        $role_employee->givePermissionTo($permission_read_vouchers);
        $role_employee->givePermissionTo($permission_update_vouchers);
        $role_employee->givePermissionTo($permission_delete_vouchers);
        $role_customer->givePermissionTo($permission_read_vouchers);

        $role_admin->givePermissionTo($permission_send_vouchers);
        $role_api->givePermissionTo($permission_send_vouchers);
        $role_owner->givePermissionTo($permission_send_vouchers);
        $role_supervisor->givePermissionTo($permission_send_vouchers);

        $role_admin->givePermissionTo($permission_report_vouchers);
        $role_api->givePermissionTo($permission_report_vouchers);
        $role_owner->givePermissionTo($permission_report_vouchers);
        $role_supervisor->givePermissionTo($permission_report_vouchers);
        $role_employee->givePermissionTo($permission_report_vouchers);
        $role_customer->givePermissionTo($permission_report_vouchers);

        $input['name'] = 'Edgar Salguero';
        $input['email'] = 'edgar.salguero@taotechideas.com';
        $input['password'] = Hash::make('edgar1234');
        $user = User::create($input);
        $user->assignRole('admin');

        DraftJson::getInstance()->appendUser($user);
    }
}
