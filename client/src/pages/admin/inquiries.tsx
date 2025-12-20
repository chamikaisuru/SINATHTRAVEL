import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getAdminInquiries, updateInquiryStatus, deleteInquiry } from "@/lib/adminApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { useToast } from "@/hooks/use-toast";
import { Mail, Phone, Trash2, Search } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

export default function AdminInquiries() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [statusFilter, setStatusFilter] = useState<string>('');
  const [search, setSearch] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['admin-inquiries', statusFilter, search],
    queryFn: () => getAdminInquiries({ status: statusFilter || undefined, search: search || undefined }),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: 'new' | 'read' | 'replied' }) =>
      updateInquiryStatus(id, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-inquiries'] });
      toast({ title: "Status updated successfully" });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteInquiry,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-inquiries'] });
      toast({ title: "Inquiry deleted successfully" });
    },
  });

  const inquiries = data?.inquiries || [];
  const stats = data?.stats;

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-heading font-bold text-primary">Inquiries</h1>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatCard label="Total" value={stats?.total || 0} />
        <StatCard label="New" value={stats?.new_count || 0} color="orange" />
        <StatCard label="Read" value={stats?.read_count || 0} color="blue" />
        <StatCard label="Replied" value={stats?.replied_count || 0} color="green" />
      </div>

      {/* Filters */}
      <div className="flex gap-4">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            placeholder="Search inquiries..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-10"
          />
        </div>
        <Select value={statusFilter} onValueChange={setStatusFilter}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="All Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Status</SelectItem>
            <SelectItem value="new">New</SelectItem>
            <SelectItem value="read">Read</SelectItem>
            <SelectItem value="replied">Replied</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {/* Inquiries List */}
      <div className="space-y-4">
        {inquiries.map((inquiry) => (
          <Card key={inquiry.id}>
            <CardContent className="p-6">
              <div className="flex items-start justify-between mb-4">
                <div className="flex-1">
                  <h3 className="font-bold text-lg">{inquiry.name}</h3>
                  <div className="flex gap-4 text-sm text-muted-foreground mt-2">
                    <span className="flex items-center gap-1">
                      <Mail className="w-4 h-4" />
                      {inquiry.email}
                    </span>
                    <span className="flex items-center gap-1">
                      <Phone className="w-4 h-4" />
                      {inquiry.phone}
                    </span>
                  </div>
                </div>
                <Badge variant={
                  inquiry.status === 'new' ? 'default' :
                  inquiry.status === 'read' ? 'secondary' : 'outline'
                }>
                  {inquiry.status}
                </Badge>
              </div>

              <p className="text-sm mb-4 p-4 bg-muted/50 rounded">{inquiry.message}</p>

              <div className="flex items-center justify-between">
                <span className="text-xs text-muted-foreground">
                  {new Date(inquiry.created_at).toLocaleString()}
                </span>
                <div className="flex gap-2">
                  {inquiry.status === 'new' && (
                    <Button size="sm" variant="outline"
                      onClick={() => updateMutation.mutate({ id: inquiry.id, status: 'read' })}>
                      Mark as Read
                    </Button>
                  )}
                  {inquiry.status === 'read' && (
                    <Button size="sm" variant="outline"
                      onClick={() => updateMutation.mutate({ id: inquiry.id, status: 'replied' })}>
                      Mark as Replied
                    </Button>
                  )}
                  <Button size="sm" variant="destructive"
                    onClick={() => {
                      if (confirm('Delete this inquiry?')) {
                        deleteMutation.mutate(inquiry.id);
                      }
                    }}>
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}

function StatCard({ label, value, color = 'blue' }: { label: string; value: number; color?: string }) {
  const colors = {
    blue: 'bg-blue-500/10 text-blue-500',
    green: 'bg-green-500/10 text-green-500',
    orange: 'bg-orange-500/10 text-orange-500',
  };

  return (
    <Card>
      <CardContent className="p-6">
        <p className="text-sm text-muted-foreground mb-2">{label}</p>
        <p className={`text-3xl font-bold ${colors[color as keyof typeof colors]}`}>{value}</p>
      </CardContent>
    </Card>
  );
}