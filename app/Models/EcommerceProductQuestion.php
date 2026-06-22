<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceProductQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ecommerce_product_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'customer_name',
        'customer_email',
        'question',
        'status',
    ];

    /**
     * Get the product that the question belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who asked the question.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the replies for the question.
     */
    public function replies()
    {
        return $this->hasMany(EcommerceProductQuestionReply::class, 'product_question_id');
    }
}
