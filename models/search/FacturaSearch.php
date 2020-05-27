<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Factura;

/**
 * FacturaSearch represents the model behind the search form of `app\models\Factura`.
 */
class FacturaSearch extends Factura
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_tiket'], 'integer'],
            [['fecha_factura', 'nroFactura', 'informada', 'fecha_informada', 'cae', 'ptoVta'], 'safe'],
            [['monto'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Factura::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'fecha_factura' => $this->fecha_factura,
            'fecha_informada' => $this->fecha_informada,
            'monto' => $this->monto,
            'id_tiket' => $this->id_tiket,
        ]);

        $query->andFilterWhere(['like', 'nroFactura', $this->nroFactura])
            ->andFilterWhere(['like', 'informada', $this->informada])
            ->andFilterWhere(['like', 'cae', $this->cae])
            ->andFilterWhere(['like', 'ptoVta', $this->ptoVta]);

        return $dataProvider;
    }
}
