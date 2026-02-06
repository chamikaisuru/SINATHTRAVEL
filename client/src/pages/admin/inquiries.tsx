import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getAdminInquiries, updateInquiryStatus, deleteInquiry } from "@/lib/adminApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useToast } from "@/hooks/use-toast";
import { Mail, Phone, Trash2, Search, Send, Copy, Check } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

interface Inquiry {
  id: number;
  name: string;
  email: string;
  phone: string;
  message: string;
  status: 'new' | 'read' | 'replied';
  created_at: string;
}

export default function AdminInquiries() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [search, setSearch] = useState('');
  const [replyDialogOpen, setReplyDialogOpen] = useState(false);
  const [selectedInquiry, setSelectedInquiry] = useState<Inquiry | null>(null);
  const [replySubject, setReplySubject] = useState('');
  const [replyMessage, setReplyMessage] = useState('');
  const [copiedEmail, setCopiedEmail] = useState(false);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-inquiries', statusFilter, search],
    queryFn: () => getAdminInquiries({
      status: statusFilter === 'all' ? undefined : statusFilter,
      search: search || undefined
    }),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: 'new' | 'read' | 'replied' }) =>
      updateInquiryStatus(id, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-inquiries'] });
      toast({ title: "✅ Status updated successfully" });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteInquiry,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-inquiries'] });
      toast({ title: "✅ Inquiry deleted successfully" });
    },
  });

  const inquiries = data?.inquiries || [];
  const stats = data?.stats;

  function handleOpenReplyDialog(inquiry: Inquiry) {
    setSelectedInquiry(inquiry);
    setReplySubject(`Re: Your inquiry from Sinath Travels`);
    setReplyMessage(`Dear ${inquiry.name},\n\nThank you for your inquiry.\n\n\n\nBest regards,\nSinath Travels Team\nEmail: clickbee@gmail.com\nPhone: ${inquiry.phone}`);
    setReplyDialogOpen(true);
  }

  function handleCopyEmail() {
    if (selectedInquiry) {
      navigator.clipboard.writeText(selectedInquiry.email);
      setCopiedEmail(true);
      toast({ title: "📋 Email copied to clipboard" });
      setTimeout(() => setCopiedEmail(false), 2000);
    }
  }

  function handleSendReply() {
    if (!selectedInquiry) return;

    // Create mailto link
    const mailtoLink = `mailto:${selectedInquiry.email}?subject=${encodeURIComponent(replySubject)}&body=${encodeURIComponent(replyMessage)}`;

    // Open default email client
    window.location.href = mailtoLink;

    // Mark as replied
    updateMutation.mutate({ id: selectedInquiry.id, status: 'replied' });

    toast({
      title: "📧 Opening email client...",
      description: "Your default email app will open with the pre-filled message"
    });

    setReplyDialogOpen(false);
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-heading font-bold text-primary">Inquiries</h1>
        <div className="text-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading inquiries...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-heading font-bold text-primary">Inquiries</h1>
        <Badge variant="outline" className="text-sm">
          📧 Emails sent to: clickbee@gmail.com
        </Badge>
      </div>

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
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="new">New</SelectItem>
            <SelectItem value="read">Read</SelectItem>
            <SelectItem value="replied">Replied</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {/* Inquiries List */}
      {inquiries.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <p className="text-muted-foreground mb-2">No inquiries found</p>
            <p className="text-sm text-muted-foreground">
              {search || statusFilter !== 'all'
                ? 'Try adjusting your filters'
                : 'Inquiries will appear here when customers contact you'}
            </p>
          </CardContent>
        </Card>
      ) : (
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
                        <a href={`mailto:${inquiry.email}`} className="hover:text-primary">
                          {inquiry.email}
                        </a>
                      </span>
                      <span className="flex items-center gap-1">
                        <Phone className="w-4 h-4" />
                        <a href={`tel:${inquiry.phone}`} className="hover:text-primary">
                          {inquiry.phone}
                        </a>
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
                    <Button size="sm" variant="default"
                      onClick={() => handleOpenReplyDialog(inquiry)}>
                      <Send className="w-4 h-4 mr-1" />
                      Reply
                    </Button>
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
      )}

      {/* Reply Dialog */}
      <Dialog open={replyDialogOpen} onOpenChange={setReplyDialogOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Reply to {selectedInquiry?.name}</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="flex items-center justify-between p-3 bg-muted/50 rounded">
              <div className="flex items-center gap-2">
                <Mail className="w-4 h-4" />
                <span className="text-sm font-medium">{selectedInquiry?.email}</span>
              </div>
              <Button size="sm" variant="outline" onClick={handleCopyEmail}>
                {copiedEmail ? (
                  <>
                    <Check className="w-4 h-4 mr-1" />
                    Copied
                  </>
                ) : (
                  <>
                    <Copy className="w-4 h-4 mr-1" />
                    Copy
                  </>
                )}
              </Button>
            </div>

            <div>
              <label className="text-sm font-medium">Subject</label>
              <Input
                value={replySubject}
                onChange={(e) => setReplySubject(e.target.value)}
                placeholder="Email subject"
              />
            </div>

            <div>
              <label className="text-sm font-medium">Message</label>
              <Textarea
                value={replyMessage}
                onChange={(e) => setReplyMessage(e.target.value)}
                rows={10}
                placeholder="Write your reply..."
              />
            </div>

            <div className="bg-blue-50 dark:bg-blue-950 p-4 rounded border border-blue-200 dark:border-blue-800">
              <p className="text-sm text-blue-800 dark:text-blue-200">
                💡 <strong>Note:</strong> This will open your default email client (Gmail, Outlook, etc.)
                with the message pre-filled. You can edit it before sending.
              </p>
            </div>

            <div className="flex gap-2 justify-end">
              <Button variant="outline" onClick={() => setReplyDialogOpen(false)}>
                Cancel
              </Button>
              <Button onClick={handleSendReply}>
                <Send className="w-4 h-4 mr-2" />
                Open Email Client
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
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
