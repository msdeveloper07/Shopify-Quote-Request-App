<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_domain',
        'first_name',
        'last_name',
        'email',
        'phone',
        'product_title',
        'product_id',
        'variant_id',
        'variant_name',
        'product_image',
        'product_url',
        'comments',
        'selected_variant',
        'variant_price',
        'quoted_price',
        'admin_notes',
        'ip_address',
        'user_agent',
        'status',
        'quantity',
        'quotation_sent_at',
        'quotation_sent_by',
        'shopify_customer_id',
    ];

    protected $casts = [
        'variant_price' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'quotation_sent_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_domain', 'shopify_domain');
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'in_progress' => '<span class="badge bg-info">In Progress</span>',
            'quotation_sent' => '<span class="badge bg-primary">Quotation Sent</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        ];
        
        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}