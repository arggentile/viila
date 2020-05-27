<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RegistroLote;

/**
 * RegistroLoteSearch represents the model behind the search form of `app\models\RegistroLote`.
 */
class RegistroLoteSearch extends RegistroLote
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_lote'], 'integer'],
            [['nombre_cliente', 'tipo_dni', 'dni', 'email', 'concepto', 'error'], 'safe'],
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
        $query = RegistroLote::find();

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
            'id_lote' => $this->id_lote,
            'monto' => $this->monto,
        ]);

        $query->andFilterWhere(['like', 'nombre_cliente', $this->nombre_cliente])
            ->andFilterWhere(['like', 'tipo_dni', $this->tipo_dni])
            ->andFilterWhere(['like', 'dni', $this->dni])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'concepto', $this->concepto])
            ->andFilterWhere(['like', 'error', $this->error]);

        return $dataProvider;
    }
}
